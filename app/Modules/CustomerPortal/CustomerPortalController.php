<?php
namespace App\Modules\CustomerPortal;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\OrderService;
use Exception;

class CustomerPortalController extends Controller {

    public function index() {
        $products = Database::fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN product_categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 
             ORDER BY p.id ASC LIMIT 8"
        );

        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC");

        $this->renderCustomerView('customer_portal/index', [
            'title'    => 'Portal Layanan Percetakan Online - PRINTMASTER',
            'products' => $products,
            'branches' => $branches
        ]);
    }

    public function track() {
        $keyword = trim(Request::input('q', ''));
        $order = null;
        $items = [];
        $deliveries = [];

        if (!empty($keyword)) {
            // Find by order number or customer phone
            $sql = "SELECT o.*, c.name as customer_name, c.phone as customer_phone, b.name as branch_name, b.phone as branch_phone
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.id
                    JOIN branches b ON o.branch_id = b.id
                    WHERE o.order_number = :kw1 OR c.phone = :kw2
                    ORDER BY o.id DESC LIMIT 1";
            $order = Database::fetch($sql, ['kw1' => $keyword, 'kw2' => $keyword]);

            if ($order) {
                $items = Database::fetchAll(
                    "SELECT oi.*, p.name as product_name 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = :oid",
                    ['oid' => $order['id']]
                );

                $deliveries = Database::fetchAll(
                    "SELECT * FROM delivery_orders WHERE order_id = :oid ORDER BY id DESC",
                    ['oid' => $order['id']]
                );
            }
        }

        $this->renderCustomerView('customer_portal/track', [
            'title'      => 'Pelacakan Status Pesanan - PRINTMASTER',
            'keyword'    => $keyword,
            'order'      => $order,
            'items'      => $items,
            'deliveries' => $deliveries
        ]);
    }

    public function orderForm() {
        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC");
        $products = Database::fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN product_categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 ORDER BY c.id ASC, p.name ASC"
        );

        $this->renderCustomerView('customer_portal/order', [
            'title'    => 'Pemesanan Cetak Online - PRINTMASTER',
            'branches' => $branches,
            'products' => $products
        ]);
    }

    public function submitOrder() {
        $customerName = Request::input('customer_name');
        $customerPhone = Request::input('customer_phone');
        $customerEmail = Request::input('customer_email');
        $customerAddress = Request::input('customer_address');
        $branchId = (int) Request::input('branch_id', 1);
        $productId = (int) Request::input('product_id');
        $quantity = max(1, (float) Request::input('quantity', 1));
        $width = (float) Request::input('width', 0);
        $length = (float) Request::input('length', 0);
        $notes = Request::input('notes');

        if (empty($customerName) || empty($customerPhone) || empty($productId)) {
            Session::flash('error', 'Nama, nomor WhatsApp, dan produk wajib diisi.');
            Response::redirect('/customer-portal/order');
        }

        // Find or create customer
        $customer = Database::fetch("SELECT * FROM customers WHERE phone = :phone LIMIT 1", ['phone' => $customerPhone]);
        if (!$customer) {
            $custId = Database::insert('customers', [
                'company_id'    => 1,
                'branch_id'     => $branchId,
                'customer_code' => 'CUST-WEB-' . substr(time(), -5),
                'name'          => $customerName,
                'phone'         => $customerPhone,
                'email'         => $customerEmail,
                'address'       => $customerAddress,
                'customer_tier' => 'regular',
                'is_active'     => 1
            ]);
        } else {
            $custId = (int) $customer['id'];
        }

        // Get product details
        $product = Database::fetch("SELECT * FROM products WHERE id = :id", ['id' => $productId]);
        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            Response::redirect('/customer-portal/order');
        }

        // Price calculation
        $unitPrice = (float) $product['base_price'];
        if ($product['pricing_type'] === 'area' && $width > 0 && $length > 0) {
            $area = ($width * $length) / 10000; // m2
            $subtotal = $area * $unitPrice * $quantity;
        } else {
            $subtotal = $unitPrice * $quantity;
        }

        $items = [
            [
                'product_id'       => $productId,
                'quantity'         => $quantity,
                'width'            => $width,
                'length'           => $length,
                'unit_price'       => $unitPrice,
                'subtotal'         => $subtotal,
                'finishing_detail' => [],
                'design_file_path' => null,
                'notes'            => $notes
            ]
        ];

        try {
            // Create order with 0 initial payment
            $orderData = OrderService::createOrder([
                'company_id'      => 1,
                'branch_id'       => $branchId,
                'customer_id'     => $custId,
                'order_channel'   => 'online',
                'delivery_type'   => 'pickup',
                'delivery_address'=> $customerAddress,
                'notes'           => 'Order via Portal Pelanggan Online: ' . $notes,
                'due_date'        => date('Y-m-d H:i:s', strtotime('+2 days')),
                'items'           => $items,
                'payments'        => [] // Unpaid initially
            ], 1); // User ID 1 (System)

            Session::flash('success', "Pesanan online Anda berhasil dibuat dengan nomor {$orderData['order_number']}!");
            Response::redirect('/customer-portal/track?q=' . urlencode($orderData['order_number']));
        } catch (Exception $e) {
            Session::flash('error', 'Gagal memproses pesanan: ' . $e->getMessage());
            Response::redirect('/customer-portal/order');
        }
    }

    private function renderCustomerView(string $viewPath, array $data = []) {
        extract($data);
        ob_start();
        require "/home/antigravity/resources/views/{$viewPath}.php";
        $content = ob_get_clean();
        require "/home/antigravity/resources/views/layouts/customer.php";
    }
}
