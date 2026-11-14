<?php
namespace App\Modules\Pricing;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\PricingService;
use App\Services\OrderService;
use Exception;

class PricingController extends Controller {
    public function index(): void {
        $this->requirePermission('pricing.calculate');

        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
        $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE is_active = 1 ORDER BY name ASC");
        $customers = Database::fetchAll("SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC");

        $this->render('pricing/index', [
            'title'      => 'Kalkulator Harga & Estimasi Biaya Produksi',
            'products'   => $products,
            'finishings' => $finishings,
            'customers'  => $customers
        ]);
    }

    public function calculateAjax(): void {
        $this->requirePermission('pricing.calculate');
        
        $params = Request::all();
        $result = PricingService::calculate($params);

        $this->json($result);
    }

    public function calculate(): void {
        $this->calculateAjax();
    }

    public function quotes(): void {
        $this->requirePermission('pricing.calculate');

        $activeBranchId = Auth::activeBranchId();
        $quotes = Database::fetchAll("
            SELECT q.*, c.name as customer_name, c.phone as customer_phone, b.name as branch_name,
                   (SELECT COUNT(*) FROM quote_items WHERE quote_id = q.id) as total_items
            FROM quotes q
            JOIN customers c ON q.customer_id = c.id
            JOIN branches b ON q.branch_id = b.id
            WHERE q.branch_id = :bid
            ORDER BY q.id DESC
        ", ['bid' => $activeBranchId]);

        $this->render('pricing/quotes', [
            'title'  => 'Daftar Penawaran Harga (Quotes)',
            'quotes' => $quotes
        ]);
    }

    public function createQuote(): void {
        $this->requirePermission('pricing.calculate');

        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
        $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE is_active = 1 ORDER BY name ASC");
        $customers = Database::fetchAll("SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC");

        $this->render('pricing/quote_create', [
            'title'      => 'Buat Penawaran Harga Baru',
            'products'   => $products,
            'finishings' => $finishings,
            'customers'  => $customers
        ]);
    }

    public function storeQuote(): void {
        $this->requirePermission('pricing.calculate');
        $this->validateCSRF();

        $activeBranchId = Auth::activeBranchId();
        $customerId = (int) Request::post('customer_id');
        $validUntil = Request::post('valid_until', date('Y-m-d', strtotime('+14 days')));
        $notes = Request::post('notes');

        $rawItems = Request::post('items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true);
        }

        if (empty($rawItems)) {
            $this->flash('error', 'Penawaran harus memiliki minimal 1 item.');
            $this->back();
        }

        $ym = date('Ym');
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM quotes WHERE quote_number LIKE 'QTE-{$ym}-%'");
        $seq = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $quoteNumber = "QTE-{$ym}-{$seq}";

        $subtotal = 0.0;
        foreach ($rawItems as $it) {
            $subtotal += (float) ($it['subtotal'] ?? 0);
        }

        Database::beginTransaction();
        try {
            $quoteId = Database::insert('quotes', [
                'company_id'   => 1,
                'branch_id'    => $activeBranchId,
                'quote_number' => $quoteNumber,
                'customer_id'  => $customerId,
                'valid_until'  => $validUntil,
                'subtotal'     => $subtotal,
                'total'        => $subtotal,
                'status'       => 'sent',
                'notes'        => $notes,
                'created_by'   => Auth::id()
            ]);

            foreach ($rawItems as $it) {
                Database::insert('quote_items', [
                    'quote_id'          => $quoteId,
                    'product_id'        => (int) $it['product_id'],
                    'item_description'  => $it['item_name'],
                    'width_cm'          => (float) ($it['width_cm'] ?? 0),
                    'height_cm'         => (float) ($it['height_cm'] ?? 0),
                    'area_sqm'          => (float) ($it['area_sqm'] ?? 0),
                    'quantity'          => (float) ($it['quantity'] ?? 1),
                    'unit'              => $it['unit'] ?? 'pcs',
                    'unit_price'        => (float) ($it['unit_price'] ?? 0),
                    'cost_estimate'     => (float) ($it['cost_estimate'] ?? 0),
                    'subtotal'          => (float) ($it['subtotal'] ?? 0),
                    'finishing_details' => $it['finishing_details'] ?? null
                ]);
            }

            Database::commit();
            $this->flash('success', "Penawaran harga {$quoteNumber} berhasil disimpan!");
            $this->redirect('/quotes');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal menyimpan penawaran: ' . $e->getMessage());
            $this->back();
        }
    }

    public function convertToOrder(string $id): void {
        $this->requirePermission('orders.create');
        $this->validateCSRF();
        $quoteId = (int) $id;

        $quote = Database::fetchOne("SELECT * FROM quotes WHERE id = :id", ['id' => $quoteId]);
        if (!$quote || $quote['status'] === 'converted') {
            $this->flash('error', 'Penawaran tidak valid atau sudah dikonversi.');
            $this->back();
        }

        $items = Database::fetchAll("SELECT * FROM quote_items WHERE quote_id = :id", ['id' => $quoteId]);

        $preparedItems = [];
        foreach ($items as $it) {
            $preparedItems[] = [
                'product_id'        => $it['product_id'],
                'item_name'         => $it['item_description'],
                'width_cm'          => $it['width_cm'],
                'height_cm'         => $it['height_cm'],
                'area_sqm'          => $it['area_sqm'],
                'quantity'          => $it['quantity'],
                'unit'              => $it['unit'],
                'unit_price'        => $it['unit_price'],
                'cost_estimate'     => $it['cost_estimate'],
                'subtotal'          => $it['subtotal'],
                'total'             => $it['subtotal'],
                'finishing_details' => $it['finishing_details']
            ];
        }

        try {
            $res = OrderService::createOrder([
                'company_id'           => 1,
                'branch_id'            => $quote['branch_id'],
                'customer_id'          => $quote['customer_id'],
                'quote_id'             => $quoteId,
                'order_date'           => date('Y-m-d'),
                'deadline_date'        => date('Y-m-d 17:00:00', strtotime('+2 days')),
                'items'                => $preparedItems,
                'initial_payment'      => 0,
                'notes'                => "Dikonversi otomatis dari Penawaran {$quote['quote_number']}"
            ], Auth::id());

            Database::update('quotes', ['status' => 'converted'], 'id = :id', ['id' => $quoteId]);

            $this->flash('success', "Penawaran {$quote['quote_number']} berhasil dikonversi menjadi Order {$res['order_number']}!");
            $this->redirect('/orders/' . $res['order_id']);
        } catch (Exception $e) {
            $this->flash('error', 'Gagal konversi penawaran: ' . $e->getMessage());
            $this->back();
        }
    }
}
