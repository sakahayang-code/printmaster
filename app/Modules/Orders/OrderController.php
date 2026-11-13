<?php
namespace App\Modules\Orders;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Exception;

class OrderController extends Controller {
    public function index(): void {
        $this->requirePermission('orders.view');

        $activeBranchId = Auth::activeBranchId();
        $isConsolidated = (Request::get('view') === 'all') && (Auth::isSuperAdmin() || Auth::isOwner());

        $branchFilter = $isConsolidated ? "1=1" : "o.branch_id = {$activeBranchId}";
        
        $status = Request::get('status');
        $paymentStatus = Request::get('payment_status');
        $search = trim(Request::get('search', ''));
        $dateFrom = Request::get('date_from');
        $dateTo = Request::get('date_to');

        $where = [$branchFilter];
        $params = [];

        if (!empty($status)) {
            $where[] = "o.status = :status";
            $params['status'] = $status;
        }

        if (!empty($paymentStatus)) {
            $where[] = "o.payment_status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }

        if (!empty($search)) {
            $where[] = "(o.order_number LIKE :s1 OR o.invoice_number LIKE :s2 OR c.name LIKE :s3 OR c.phone LIKE :s4)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
            $params['s4'] = "%{$search}%";
        }

        if (!empty($dateFrom)) {
            $where[] = "o.order_date >= :dfrom";
            $params['dfrom'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where[] = "o.order_date <= :dto";
            $params['dto'] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, b.name as branch_name,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            WHERE {$whereClause}
            ORDER BY o.id DESC
        ";

        $orders = Database::fetchAll($sql, $params);

        $this->render('orders/index', [
            'title'          => 'Daftar Pesanan & Penjualan',
            'orders'         => $orders,
            'isConsolidated' => $isConsolidated,
            'status'         => $status,
            'paymentStatus'  => $paymentStatus,
            'search'         => $search,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo
        ]);
    }

    public function create(): void {
        $this->requirePermission('orders.create');

        $activeBranchId = Auth::activeBranchId();
        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
        $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE is_active = 1 ORDER BY name ASC");
        $customers = Database::fetchAll("SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC");

        $this->render('orders/create', [
            'title'      => 'Buat Order Percetakan Baru',
            'products'   => $products,
            'finishings' => $finishings,
            'customers'  => $customers
        ]);
    }

    public function store(): void {
        $this->requirePermission('orders.create');
        $this->validateCSRF();

        $activeBranchId = Auth::activeBranchId();
        $userId = Auth::id();

        $customerId = (int) Request::post('customer_id');
        $deadline   = Request::post('deadline_date') . ' ' . Request::post('deadline_time', '17:00:00');
        $pickupType = Request::post('pickup_delivery_type', 'pickup');
        $shippingAddr = Request::post('shipping_address');
        $notes      = Request::post('notes');
        $voucherCode = trim(Request::post('voucher_code', ''));
        $initialPay = (float) Request::post('initial_payment', 0);
        $payMethod  = Request::post('payment_method', 'cash');
        $bankRef    = Request::post('bank_reference');

        $rawItems = Request::post('items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true);
        }

        if (empty($rawItems)) {
            $this->flash('error', 'Pesanan harus memiliki minimal satu item produk.');
            $this->back();
        }

        try {
            $result = OrderService::createOrder([
                'company_id'           => 1,
                'branch_id'            => $activeBranchId,
                'customer_id'          => $customerId,
                'order_date'           => Request::post('order_date', date('Y-m-d')),
                'deadline_date'        => $deadline,
                'pickup_delivery_type' => $pickupType,
                'shipping_address'     => $shippingAddr,
                'voucher_code'         => $voucherCode,
                'notes'                => $notes,
                'items'                => $rawItems,
                'initial_payment'      => $initialPay,
                'payment_method'       => $payMethod,
                'bank_reference'       => $bankRef
            ], $userId);

            $this->flash('success', "Order berhasil dibuat! Nomor: {$result['order_number']}");
            $this->redirect('/orders/' . $result['order_id']);
        } catch (Exception $e) {
            $this->flash('error', 'Gagal membuat order: ' . $e->getMessage());
            $this->back();
        }
    }

    public function show(string $id): void {
        $this->requirePermission('orders.view');
        $orderId = (int) $id;

        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
                   c.customer_tier, c.address as customer_address, b.name as branch_name, b.code as branch_code,
                   u.name as creator_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            LEFT JOIN users u ON o.created_by = u.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if (!$order) {
            $this->flash('error', 'Pesanan tidak ditemukan.');
            $this->redirect('/orders');
        }

        $this->requireBranchAccess($order['branch_id']);

        $items = Database::fetchAll("
            SELECT oi.*, p.code as product_code, p.pricing_mode
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :id
        ", ['id' => $orderId]);

        $payments = Database::fetchAll("
            SELECT p.*, u.name as verifier_name
            FROM payments p
            LEFT JOIN users u ON p.verified_by = u.id
            WHERE p.order_id = :id
            ORDER BY p.id ASC
        ", ['id' => $orderId]);

        $jobs = Database::fetchAll("
            SELECT pj.*, m.name as machine_name, u.name as operator_name,
                   sb.name as source_branch_name, tb.name as target_branch_name
            FROM production_jobs pj
            LEFT JOIN machines m ON pj.machine_id = m.id
            LEFT JOIN users u ON pj.operator_id = u.id
            LEFT JOIN branches sb ON pj.source_branch_id = sb.id
            LEFT JOIN branches tb ON pj.target_branch_id = tb.id
            WHERE pj.order_id = :id
        ", ['id' => $orderId]);

        $files = Database::fetchAll("
            SELECT f.*, u.name as uploader_name 
            FROM order_files f
            LEFT JOIN users u ON f.uploaded_by = u.id
            WHERE f.order_id = :id
        ", ['id' => $orderId]);

        $history = Database::fetchAll("
            SELECT h.*, u.name as changer_name
            FROM order_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.order_id = :id
            ORDER BY h.id ASC
        ", ['id' => $orderId]);

        $this->render('orders/show', [
            'title'    => "Detail Order: {$order['order_number']}",
            'order'    => $order,
            'items'    => $items,
            'payments' => $payments,
            'jobs'     => $jobs,
            'files'    => $files,
            'history'  => $history
        ]);
    }

    public function updateStatus(string $id): void {
        $this->requirePermission('orders.edit');
        $this->validateCSRF();
        $orderId = (int) $id;

        $newStatus = Request::post('status');
        $notes = Request::post('notes', 'Perubahan status pesanan');

        $order = Database::fetchOne("SELECT * FROM orders WHERE id = :id", ['id' => $orderId]);
        if (!$order) {
            $this->flash('error', 'Order tidak ditemukan.');
            $this->back();
        }

        $this->requireBranchAccess($order['branch_id']);

        Database::beginTransaction();
        try {
            Database::update('orders', ['status' => $newStatus], 'id = :id', ['id' => $orderId]);
            Database::insert('order_status_history', [
                'order_id'   => $orderId,
                'old_status' => $order['status'],
                'new_status' => $newStatus,
                'changed_by' => Auth::id(),
                'notes'      => $notes
            ]);
            Database::commit();

            // Notify customer if ready
            if ($newStatus === 'ready_for_pickup') {
                WhatsAppService::sendOrderReady($orderId);
            }

            $this->flash('success', "Status order berhasil diubah menjadi: " . ucfirst(str_replace('_', ' ', $newStatus)));
            $this->back();
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
            $this->back();
        }
    }

    public function addPayment(string $id): void {
        $this->requirePermission('payments.receive');
        $this->validateCSRF();
        $orderId = (int) $id;

        $amount = (float) Request::post('amount');
        $method = Request::post('payment_method', 'cash');
        $ref = Request::post('reference_number');
        $notes = Request::post('notes');

        try {
            $res = OrderService::addPayment([
                'order_id'         => $orderId,
                'amount'           => $amount,
                'payment_method'   => $method,
                'reference_number' => $ref,
                'notes'            => $notes
            ], Auth::id());

            $this->flash('success', "Pembayaran sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat! Status: {$res['payment_status']}");
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
            $this->back();
        }
    }

    public function printInvoice(string $id): void {
        $this->requirePermission('orders.view');
        $orderId = (int) $id;

        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   b.name as branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
                   comp.name as company_name, comp.tax_number
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            JOIN companies comp ON o.company_id = comp.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if (!$order) die("Order tidak ditemukan");

        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :id", ['id' => $orderId]);
        $payments = Database::fetchAll("SELECT * FROM payments WHERE order_id = :id AND status = 'verified'", ['id' => $orderId]);

        $this->render('orders/invoice', [
            'title'    => "Invoice {$order['invoice_number']}",
            'order'    => $order,
            'items'    => $items,
            'payments' => $payments
        ], 'print');
    }

    public function printReceipt(string $id): void {
        $this->requirePermission('orders.view');
        $orderId = (int) $id;

        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone,
                   b.name as branch_name, b.address as branch_address, b.phone as branch_phone,
                   comp.name as company_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            JOIN companies comp ON o.company_id = comp.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if (!$order) die("Order tidak ditemukan");

        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :id", ['id' => $orderId]);
        $payments = Database::fetchAll("SELECT * FROM payments WHERE order_id = :id AND status = 'verified'", ['id' => $orderId]);

        $this->render('orders/receipt', [
            'title'    => "Struk Kasir {$order['order_number']}",
            'order'    => $order,
            'items'    => $items,
            'payments' => $payments
        ], 'print');
    }

    public function printWorkOrder(string $id): void {
        $this->requirePermission('orders.view');
        $orderId = (int) $id;

        $order = Database::fetchOne("
            SELECT o.*, c.name as customer_name, b.name as branch_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            WHERE o.id = :id
        ", ['id' => $orderId]);

        if (!$order) die("Order tidak ditemukan");

        $items = Database::fetchAll("
            SELECT oi.*, pj.job_number, m.name as machine_name, u.name as operator_name
            FROM order_items oi
            LEFT JOIN production_jobs pj ON oi.id = pj.order_item_id
            LEFT JOIN machines m ON pj.machine_id = m.id
            LEFT JOIN users u ON pj.operator_id = u.id
            WHERE oi.order_id = :id
        ", ['id' => $orderId]);

        $this->render('orders/work_order', [
            'title' => "Surat Perintah Kerja (SPK) {$order['order_number']}",
            'order' => $order,
            'items' => $items
        ], 'print');
    }
}
