<?php
namespace App\Modules\POS;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\OrderService;
use Exception;

class POSController extends Controller {
    public function index(): void {
        $this->requirePermission('orders.create');

        $activeBranchId = Auth::activeBranchId();

        // Check active cash shift for current branch
        $shift = Database::fetchOne(
            "SELECT * FROM cash_shifts WHERE branch_id = :bid AND status = 'open' ORDER BY id DESC LIMIT 1",
            ['bid' => $activeBranchId]
        );

        $categories = Database::fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order ASC");
        $products = Database::fetchAll("
            SELECT p.*, pc.name as category_name 
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.id
            WHERE p.is_active = 1
            ORDER BY pc.sort_order ASC, p.name ASC
        ");
        $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE is_active = 1 ORDER BY name ASC");
        $customers = Database::fetchAll("SELECT id, customer_code, name, phone, customer_tier, discount_percent FROM customers WHERE is_active = 1 ORDER BY name ASC");
        $promotions = Database::fetchAll("SELECT * FROM promotions WHERE is_active = 1 AND CURDATE() BETWEEN start_date AND end_date");

        $this->render('pos/index', [
            'title'      => 'Kasir POS Penjualan',
            'shift'      => $shift,
            'categories' => $categories,
            'products'   => $products,
            'finishings' => $finishings,
            'customers'  => $customers,
            'promotions' => $promotions
        ]);
    }

    public function checkout(): void {
        $this->requirePermission('orders.create');
        $this->validateCSRF();

        $activeBranchId = Auth::activeBranchId();
        $userId = Auth::id();

        // Ensure open cash shift exists
        $shift = Database::fetchOne("SELECT * FROM cash_shifts WHERE branch_id = :bid AND status = 'open' LIMIT 1", ['bid' => $activeBranchId]);
        if (!$shift) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Shift kasir belum dibuka! Silakan buka shift kasir terlebih dahulu.'], 400);
            }
            $this->flash('error', 'Shift kasir belum dibuka!');
            $this->back();
        }

        $customerId = (int) Request::input('customer_id', 1);
        $voucherCode = trim(Request::input('voucher_code', ''));
        $paymentMethod = Request::input('payment_method', 'cash');
        $amountPaid = (float) Request::input('amount_paid', 0);
        $bankRef = Request::input('bank_reference', null);
        $notes = Request::input('notes', 'Transaksi POS Kasir');

        $rawItems = Request::input('items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true);
        }

        if (empty($rawItems) || !is_array($rawItems)) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Keranjang belanja masih kosong.'], 400);
            }
            $this->flash('error', 'Keranjang belanja masih kosong.');
            $this->back();
        }

        // Prepare order items
        $preparedItems = [];
        foreach ($rawItems as $it) {
            $preparedItems[] = [
                'product_id'        => (int) $it['product_id'],
                'item_name'         => $it['item_name'],
                'description'       => $it['description'] ?? null,
                'width_cm'          => (float) ($it['width_cm'] ?? 0),
                'height_cm'         => (float) ($it['height_cm'] ?? 0),
                'area_sqm'          => (float) ($it['area_sqm'] ?? 0),
                'quantity'          => (float) ($it['quantity'] ?? 1),
                'unit'              => $it['unit'] ?? 'pcs',
                'unit_price'        => (float) ($it['unit_price'] ?? 0),
                'cost_estimate'     => (float) ($it['cost_estimate'] ?? 0),
                'subtotal'          => (float) ($it['subtotal'] ?? 0),
                'discount'          => (float) ($it['discount'] ?? 0),
                'tax'               => 0.00,
                'total'             => (float) ($it['subtotal'] ?? 0),
                'finishing_details' => $it['finishing_details'] ?? null,
                'specs_json'        => $it['specs_json'] ?? null,
                'price_snapshot'    => $it
            ];
        }

        try {
            $result = OrderService::createOrder([
                'company_id'           => 1,
                'branch_id'            => $activeBranchId,
                'customer_id'          => $customerId,
                'order_date'           => date('Y-m-d'),
                'deadline_date'        => date('Y-m-d H:i:s', strtotime('+4 hours')),
                'pickup_delivery_type' => 'pickup',
                'voucher_code'         => $voucherCode,
                'notes'                => $notes,
                'items'                => $preparedItems,
                'initial_payment'      => $amountPaid,
                'payment_method'       => $paymentMethod,
                'bank_reference'       => $bankRef
            ], $userId);

            if (Request::isAjax()) {
                $this->json([
                    'success'        => true,
                    'order_id'       => $result['order_id'],
                    'order_number'   => $result['order_number'],
                    'invoice_number' => $result['invoice_number'],
                    'total_amount'   => $result['total_amount'],
                    'receipt_url'    => '/orders/' . $result['order_id'] . '/receipt'
                ]);
            }

            $this->flash('success', "Transaksi berhasil dibuat! No Order: {$result['order_number']}");
            $this->redirect('/orders/' . $result['order_id'] . '/receipt');
        } catch (Exception $e) {
            if (Request::isAjax()) {
                $this->json(['error' => $e->getMessage()], 400);
            }
            $this->flash('error', 'Gagal memproses transaksi: ' . $e->getMessage());
            $this->back();
        }
    }
}
