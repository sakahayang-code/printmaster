<?php
namespace App\Modules\Purchasing;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Exception;

class PurchasingController extends Controller {
    public function index(): void {
        $this->requirePermission('purchasing.view');

        $activeBranchId = Auth::activeBranchId();

        $orders = Database::fetchAll("
            SELECT po.*, s.name as supplier_name, s.phone as supplier_phone, b.name as branch_name,
                   (SELECT COUNT(*) FROM purchase_order_items WHERE purchase_order_id = po.id) as total_items
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN branches b ON po.branch_id = b.id
            WHERE po.branch_id = :bid
            ORDER BY po.id DESC
        ", ['bid' => $activeBranchId]);

        $suppliers = Database::fetchAll("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC");
        $materials = Database::fetchAll("SELECT * FROM materials WHERE is_active = 1 ORDER BY name ASC");
        $warehouses = Database::fetchAll("SELECT * FROM warehouses WHERE branch_id = :bid AND is_active = 1", ['bid' => $activeBranchId]);

        $this->render('purchasing/index', [
            'title'      => 'Pembelian Bahan & PO Supplier',
            'orders'     => $orders,
            'suppliers'  => $suppliers,
            'materials'  => $materials,
            'warehouses' => $warehouses
        ]);
    }

    public function store(): void {
        $this->requirePermission('purchasing.manage');
        $this->validateCSRF();

        $activeBranchId = Auth::activeBranchId();
        $supplierId = (int) Request::post('supplier_id');
        $warehouseId = (int) Request::post('warehouse_id');
        $poDate = Request::post('po_date', date('Y-m-d'));
        $dueDate = Request::post('due_date', date('Y-m-d', strtotime('+30 days')));
        $notes = trim(Request::post('notes'));

        $materialId = (int) Request::post('material_id');
        $qty = (float) Request::post('quantity');
        $unitPrice = (float) Request::post('unit_price');
        $subtotal = $qty * $unitPrice;

        $ym = date('Ym');
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM purchase_orders WHERE po_number LIKE 'PO-{$ym}-%'");
        $seq = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $poNumber = "PO-{$ym}-{$seq}";

        Database::beginTransaction();
        try {
            $poId = Database::insert('purchase_orders', [
                'company_id'     => 1,
                'branch_id'      => $activeBranchId,
                'supplier_id'    => $supplierId,
                'warehouse_id'   => $warehouseId,
                'po_number'      => $poNumber,
                'po_date'        => $poDate,
                'due_date'       => $dueDate,
                'status'         => 'approved',
                'subtotal'       => $subtotal,
                'total_amount'   => $subtotal,
                'payment_status' => 'unpaid',
                'notes'          => $notes,
                'created_by'     => Auth::id()
            ]);

            $mat = Database::fetchOne("SELECT * FROM materials WHERE id = :id", ['id' => $materialId]);

            Database::insert('purchase_order_items', [
                'purchase_order_id' => $poId,
                'material_id'       => $materialId,
                'quantity'          => $qty,
                'unit'              => $mat['unit'] ?? 'pcs',
                'unit_price'        => $unitPrice,
                'subtotal'          => $subtotal
            ]);

            Database::commit();
            $this->flash('success', "Purchase Order {$poNumber} berhasil dibuat!");
            $this->redirect('/purchasing');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal membuat PO: ' . $e->getMessage());
            $this->back();
        }
    }

    public function receive(string $id): void {
        $this->requirePermission('purchasing.manage');
        $this->validateCSRF();
        $poId = (int) $id;

        Database::beginTransaction();
        try {
            $po = Database::fetchOne("SELECT * FROM purchase_orders WHERE id = :id", ['id' => $poId]);
            if (!$po || $po['status'] === 'received') {
                throw new Exception("PO tidak ditemukan atau sudah diterima.");
            }

            $items = Database::fetchAll("SELECT * FROM purchase_order_items WHERE purchase_order_id = :id", ['id' => $poId]);

            // Create Goods Receipt
            $rcpCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM goods_receipts");
            $receiptNumber = 'GR-' . date('Ym') . '-' . str_pad($rcpCount + 1, 4, '0', STR_PAD_LEFT);

            $grId = Database::insert('goods_receipts', [
                'purchase_order_id'    => $poId,
                'branch_id'            => $po['branch_id'],
                'warehouse_id'         => $po['warehouse_id'],
                'receipt_number'       => $receiptNumber,
                'receipt_date'         => date('Y-m-d'),
                'delivery_note_number' => Request::post('delivery_note_number', 'SJ-' . time()),
                'received_by'          => Auth::id(),
                'notes'                => 'Penerimaan barang fisik dari supplier'
            ]);

            foreach ($items as $it) {
                Database::insert('goods_receipt_items', [
                    'goods_receipt_id'  => $grId,
                    'material_id'       => $it['material_id'],
                    'quantity_received' => $it['quantity'],
                    'unit'              => $it['unit']
                ]);

                // Update warehouse stock
                InventoryService::recordMovement(
                    $po['branch_id'],
                    $po['warehouse_id'],
                    $it['material_id'],
                    'purchase_in',
                    (float) $it['quantity'],
                    (float) $it['unit_price'],
                    'purchase_order',
                    $poId,
                    "Penerimaan PO {$po['po_number']} ({$receiptNumber})",
                    Auth::id()
                );
            }

            Database::update('purchase_orders', ['status' => 'received'], 'id = :id', ['id' => $poId]);

            // Post Accounting Journal:
            // Dr. Persediaan Bahan Baku (1140)
            // Cr. Hutang Dagang Supplier (2110)
            $entries = [
                ['account_id' => 7, 'debit' => (float)$po['total_amount'], 'credit' => 0.00, 'memo' => "Persediaan masuk dari PO {$po['po_number']}"],
                ['account_id' => 14, 'debit' => 0.00, 'credit' => (float)$po['total_amount'], 'memo' => "Hutang dagang pembelian {$po['po_number']}"]
            ];

            AccountingService::postJournal(
                $po['branch_id'],
                date('Y-m-d'),
                'purchase',
                $poId,
                "Penerimaan pembelian PO {$po['po_number']}",
                $entries,
                Auth::id(),
                $po['company_id']
            );

            Database::commit();
            $this->flash('success', "Barang dari PO {$po['po_number']} berhasil diterima, stok gudang bertambah, dan hutang supplier dicatat di jurnal!");
            $this->redirect('/purchasing');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage());
            $this->back();
        }
    }

    public function suppliers(): void {
        $this->requirePermission('purchasing.view');

        $suppliers = Database::fetchAll("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC");

        $this->render('purchasing/suppliers', [
            'title'     => 'Master Supplier & Pemasok',
            'suppliers' => $suppliers
        ]);
    }

    public function storeSupplier(): void {
        $this->requirePermission('purchasing.manage');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $pic = trim(Request::post('contact_person'));
        $phone = trim(Request::post('phone'));
        $email = trim(Request::post('email')) ?: null;
        $addr = trim(Request::post('address'));
        $terms = (int) Request::post('payment_terms_days', 30);

        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM suppliers");
        $code = 'SUP-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        try {
            Database::insert('suppliers', [
                'company_id'         => 1,
                'code'               => $code,
                'name'               => $name,
                'contact_person'     => $pic,
                'phone'              => $phone,
                'email'              => $email,
                'address'            => $addr,
                'payment_terms_days' => $terms,
                'is_active'          => 1
            ]);

            $this->flash('success', "Supplier '{$name}' ({$code}) berhasil ditambahkan!");
            $this->redirect('/suppliers');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambah supplier: ' . $e->getMessage());
            $this->back();
        }
    }
}
