<?php
namespace App\Modules\Inventory;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\InventoryService;
use Exception;

class InventoryController extends Controller {
    public function index(): void {
        $this->requirePermission('inventory.view');

        $category = Request::get('category');
        $search = trim(Request::get('search', ''));

        $where = ["is_active = 1"];
        $params = [];

        if (!empty($category)) {
            $where[] = "category = :cat";
            $params['cat'] = $category;
        }

        if (!empty($search)) {
            $where[] = "(name LIKE :s1 OR code LIKE :s2)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $materials = Database::fetchAll("
            SELECT * FROM materials 
            WHERE " . implode(' AND ', $where) . " 
            ORDER BY current_stock <= min_stock DESC, name ASC
        ", $params);

        $warehouses = Database::fetchAll("SELECT * FROM warehouses WHERE is_active = 1");

        $this->render('inventory/index', [
            'title'      => 'Stok Bahan Baku & Gudang',
            'materials'  => $materials,
            'warehouses' => $warehouses,
            'category'   => $category,
            'search'     => $search
        ]);
    }

    public function storeMaterial(): void {
        $this->requirePermission('inventory.view');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $cat = Request::post('category', 'paper');
        $unit = Request::post('unit', 'pcs');
        $min = (float) Request::post('min_stock', 10);
        $initStock = (float) Request::post('initial_stock', 0);
        $avgCost = (float) Request::post('avg_cost', 0);

        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM materials");
        $code = 'MAT-' . strtoupper(substr($cat, 0, 3)) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        try {
            $matId = Database::insert('materials', [
                'company_id'          => 1,
                'code'                => $code,
                'name'                => $name,
                'category'            => $cat,
                'unit'                => $unit,
                'min_stock'           => $min,
                'current_stock'       => $initStock,
                'avg_cost'            => $avgCost,
                'last_purchase_price' => $avgCost,
                'is_active'           => 1
            ]);

            if ($initStock > 0) {
                // Record initial stock movement
                InventoryService::recordMovement(
                    Auth::activeBranchId(),
                    1,
                    $matId,
                    'purchase_in',
                    $initStock,
                    $avgCost,
                    'manual',
                    null,
                    'Saldo awal bahan baku',
                    Auth::id()
                );
            }

            $this->flash('success', "Bahan baku '{$name}' ({$code}) berhasil ditambahkan!");
            $this->redirect('/inventory');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambah bahan: ' . $e->getMessage());
            $this->back();
        }
    }

    public function movements(): void {
        $this->requirePermission('inventory.view');

        $activeBranchId = Auth::activeBranchId();
        $materialId = Request::get('material_id');

        $where = ["sm.branch_id = :bid"];
        $params = ['bid' => $activeBranchId];

        if (!empty($materialId)) {
            $where[] = "sm.material_id = :mid";
            $params['mid'] = $materialId;
        }

        $movements = Database::fetchAll("
            SELECT sm.*, m.name as material_name, m.code as material_code, m.unit,
                   w.name as warehouse_name, u.name as actor_name
            FROM stock_movements sm
            JOIN materials m ON sm.material_id = m.id
            JOIN warehouses w ON sm.warehouse_id = w.id
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY sm.id DESC LIMIT 50
        ", $params);

        $materials = Database::fetchAll("SELECT id, name, code, unit FROM materials WHERE is_active = 1 ORDER BY name ASC");

        $this->render('inventory/movements', [
            'title'      => 'Kartu Stok & Mutasi Bahan Baku',
            'movements'  => $movements,
            'materials'  => $materials,
            'materialId' => $materialId
        ]);
    }

    public function adjustStock(): void {
        $this->requirePermission('inventory.adjust');
        $this->validateCSRF();

        $materialId = (int) Request::post('material_id');
        $physicalStock = (float) Request::post('physical_stock');
        $reason = trim(Request::post('adjustment_reason', 'Penyesuaian stok fisik'));

        try {
            $mat = Database::fetchOne("SELECT * FROM materials WHERE id = :id", ['id' => $materialId]);
            if (!$mat) throw new Exception("Bahan tidak ditemukan");

            $diff = $physicalStock - (float) $mat['current_stock'];
            if ($diff == 0) {
                $this->flash('info', 'Stok fisik sama dengan sistem, tidak ada perubahan.');
                $this->back();
            }

            InventoryService::recordMovement(
                Auth::activeBranchId(),
                1,
                $materialId,
                'adjustment',
                $diff,
                (float) $mat['avg_cost'],
                'stock_opname',
                null,
                "Opname: {$reason} (Selisih: {$diff} {$mat['unit']})",
                Auth::id()
            );

            $this->flash('success', "Stok bahan '{$mat['name']}' berhasil disesuaikan menjadi {$physicalStock} {$mat['unit']}.");
            $this->redirect('/inventory');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menyesuaikan stok: ' . $e->getMessage());
            $this->back();
        }
    }

    public function transfers(): void {
        $this->requirePermission('inventory.transfer');

        $activeBranchId = Auth::activeBranchId();

        $transfers = Database::fetchAll("
            SELECT st.*, fb.name as from_branch_name, tb.name as to_branch_name,
                   fw.name as from_warehouse_name, tw.name as to_warehouse_name,
                   req.name as requester_name
            FROM stock_transfers st
            JOIN branches fb ON st.from_branch_id = fb.id
            JOIN branches tb ON st.to_branch_id = tb.id
            JOIN warehouses fw ON st.from_warehouse_id = fw.id
            JOIN warehouses tw ON st.to_warehouse_id = tw.id
            LEFT JOIN users req ON st.requested_by = req.id
            WHERE st.from_branch_id = :b1 OR st.to_branch_id = :b2
            ORDER BY st.id DESC
        ", ['b1' => $activeBranchId, 'b2' => $activeBranchId]);

        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1");
        $warehouses = Database::fetchAll("SELECT * FROM warehouses WHERE is_active = 1");
        $materials = Database::fetchAll("SELECT * FROM materials WHERE is_active = 1");

        $this->render('inventory/transfers', [
            'title'      => 'Transfer Stok Antar-Cabang',
            'transfers'  => $transfers,
            'branches'   => $branches,
            'warehouses' => $warehouses,
            'materials'  => $materials
        ]);
    }

    public function storeTransfer(): void {
        $this->requirePermission('inventory.transfer');
        $this->validateCSRF();

        $fromBranchId = Auth::activeBranchId();
        $toBranchId = (int) Request::post('to_branch_id');
        $fromWhId = (int) Request::post('from_warehouse_id');
        $toWhId = (int) Request::post('to_warehouse_id');
        $materialId = (int) Request::post('material_id');
        $qty = (float) Request::post('quantity');
        $notes = trim(Request::post('notes'));

        if ($fromBranchId === $toBranchId) {
            $this->flash('error', 'Cabang asal dan tujuan tidak boleh sama.');
            $this->back();
        }

        $ym = date('Ym');
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM stock_transfers WHERE transfer_number LIKE 'TRF-{$ym}-%'");
        $seq = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $transferNumber = "TRF-{$ym}-{$seq}";

        Database::beginTransaction();
        try {
            $transferId = Database::insert('stock_transfers', [
                'company_id'        => 1,
                'from_branch_id'    => $fromBranchId,
                'to_branch_id'      => $toBranchId,
                'from_warehouse_id' => $fromWhId,
                'to_warehouse_id'   => $toWhId,
                'transfer_number'   => $transferNumber,
                'status'            => 'shipped', // Directly shipped from source
                'requested_by'      => Auth::id(),
                'transfer_date'     => date('Y-m-d'),
                'notes'             => $notes
            ]);

            $mat = Database::fetchOne("SELECT * FROM materials WHERE id = :id", ['id' => $materialId]);

            Database::insert('stock_transfer_items', [
                'stock_transfer_id' => $transferId,
                'material_id'       => $materialId,
                'quantity_sent'     => $qty,
                'unit'              => $mat['unit'] ?? 'pcs'
            ]);

            // Deduct stock from sending warehouse
            InventoryService::recordMovement(
                $fromBranchId,
                $fromWhId,
                $materialId,
                'transfer_out',
                $qty,
                (float) $mat['avg_cost'],
                'stock_transfer',
                $transferId,
                "Transfer keluar {$transferNumber} menuju cabang lain",
                Auth::id()
            );

            Database::commit();
            $this->flash('success', "Transfer stok {$transferNumber} berhasil dikirim (in-transit).");
            $this->redirect('/inventory/transfers');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal membuat transfer: ' . $e->getMessage());
            $this->back();
        }
    }

    public function receiveTransfer(string $id): void {
        $this->requirePermission('inventory.transfer');
        $this->validateCSRF();
        $transferId = (int) $id;

        Database::beginTransaction();
        try {
            $trf = Database::fetchOne("SELECT * FROM stock_transfers WHERE id = :id", ['id' => $transferId]);
            if (!$trf || $trf['status'] === 'received') {
                throw new Exception("Transfer tidak ditemukan atau sudah diterima.");
            }

            $items = Database::fetchAll("SELECT * FROM stock_transfer_items WHERE stock_transfer_id = :id", ['id' => $transferId]);
            foreach ($items as $it) {
                // Add stock to receiving warehouse
                $mat = Database::fetchOne("SELECT * FROM materials WHERE id = :id", ['id' => $it['material_id']]);
                InventoryService::recordMovement(
                    $trf['to_branch_id'],
                    $trf['to_warehouse_id'],
                    $it['material_id'],
                    'transfer_in',
                    (float) $it['quantity_sent'],
                    (float) $mat['avg_cost'],
                    'stock_transfer',
                    $transferId,
                    "Penerimaan transfer {$trf['transfer_number']} dari cabang asal",
                    Auth::id()
                );

                Database::update('stock_transfer_items', [
                    'quantity_received' => $it['quantity_sent']
                ], 'id = :id', ['id' => $it['id']]);
            }

            Database::update('stock_transfers', [
                'status'        => 'received',
                'received_by'   => Auth::id(),
                'received_date' => date('Y-m-d')
            ], 'id = :id', ['id' => $transferId]);

            Database::commit();
            $this->flash('success', "Transfer stok {$trf['transfer_number']} berhasil diterima dan ditambahkan ke gudang tujuan.");
            $this->redirect('/inventory/transfers');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal menerima transfer: ' . $e->getMessage());
            $this->back();
        }
    }
}
