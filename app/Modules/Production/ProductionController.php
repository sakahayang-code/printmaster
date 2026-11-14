<?php
namespace App\Modules\Production;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\InventoryService;
use Exception;

class ProductionController extends Controller {
    public function index(): void {
        $this->requirePermission('production.view');

        $activeBranchId = Auth::activeBranchId();

        // Jobs allocated to this branch (either source or target branch)
        $jobs = Database::fetchAll("
            SELECT pj.*, o.order_number, o.deadline_date, oi.item_name, oi.quantity as item_qty, oi.unit as item_unit,
                   oi.width_cm, oi.height_cm, oi.finishing_details,
                   m.name as machine_name, u.name as operator_name,
                   sb.name as source_branch_name, tb.name as target_branch_name
            FROM production_jobs pj
            JOIN orders o ON pj.order_id = o.id
            JOIN order_items oi ON pj.order_item_id = oi.id
            LEFT JOIN machines m ON pj.machine_id = m.id
            LEFT JOIN users u ON pj.operator_id = u.id
            LEFT JOIN branches sb ON pj.source_branch_id = sb.id
            LEFT JOIN branches tb ON pj.target_branch_id = tb.id
            WHERE (pj.target_branch_id = :bid OR pj.source_branch_id = :bid2)
            ORDER BY pj.priority = 'urgent' DESC, pj.priority = 'high' DESC, pj.id ASC
        ", ['bid' => $activeBranchId, 'bid2' => $activeBranchId]);

        // Group into Kanban stages
        $kanban = [
            'queued'      => [],
            'in_progress' => [],
            'finishing'   => [],
            'completed'   => []
        ];

        foreach ($jobs as $job) {
            $st = $job['status'];
            if ($st === 'scheduled') $st = 'queued';
            if (isset($kanban[$st])) {
                $kanban[$st][] = $job;
            } else {
                $kanban['queued'][] = $job;
            }
        }

        $machines = Database::fetchAll("SELECT * FROM machines WHERE branch_id = :bid AND is_active = 1", ['bid' => $activeBranchId]);
        $operators = Database::fetchAll("
            SELECT u.* FROM users u 
            JOIN user_branches ub ON u.id = ub.user_id 
            WHERE ub.branch_id = :bid AND u.role_id IN (7, 6, 1) AND u.is_active = 1
        ", ['bid' => $activeBranchId]);
        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1");
        $materials = Database::fetchAll("SELECT * FROM materials WHERE is_active = 1");

        $this->render('production/index', [
            'title'     => 'Antrean & Papan Produksi',
            'jobs'      => $jobs,
            'kanban'    => $kanban,
            'machines'  => $machines,
            'operators' => $operators,
            'branches'  => $branches,
            'materials' => $materials
        ]);
    }

    public function assign(string $id): void {
        $this->requirePermission('production.manage');
        $this->validateCSRF();
        $jobId = (int) $id;

        $machineId = Request::post('machine_id') ? (int) Request::post('machine_id') : null;
        $operatorId = Request::post('operator_id') ? (int) Request::post('operator_id') : null;
        $priority = Request::post('priority', 'normal');

        try {
            Database::update('production_jobs', [
                'machine_id'  => $machineId,
                'operator_id' => $operatorId,
                'priority'    => $priority
            ], 'id = :id', ['id' => $jobId]);

            $this->flash('success', 'Penugasan mesin & operator berhasil diperbarui.');
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menetapkan penugasan: ' . $e->getMessage());
            $this->back();
        }
    }

    public function updateStatus(string $id): void {
        $this->requirePermission('production.execute');
        $this->validateCSRF();
        $jobId = (int) $id;

        $newStatus = Request::post('status');
        $startTime = ($newStatus === 'in_progress') ? date('Y-m-d H:i:s') : null;
        $endTime = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;

        $updateData = ['status' => $newStatus];
        if ($startTime) $updateData['start_time'] = $startTime;
        if ($endTime) $updateData['end_time'] = $endTime;

        try {
            Database::update('production_jobs', $updateData, 'id = :id', ['id' => $jobId]);

            $job = Database::fetchOne("SELECT * FROM production_jobs WHERE id = :id", ['id' => $jobId]);
            if ($job) {
                // If job is in_progress, set order status to in_production
                if ($newStatus === 'in_progress') {
                    Database::update('orders', ['status' => 'in_production'], 'id = :id', ['id' => $job['order_id']]);
                } elseif ($newStatus === 'completed') {
                    // Check if all jobs of this order are completed
                    $incomplete = (int) Database::fetchColumn("SELECT COUNT(*) FROM production_jobs WHERE order_id = :oid AND status != 'completed'", ['oid' => $job['order_id']]);
                    if ($incomplete === 0) {
                        Database::update('orders', ['status' => 'quality_control'], 'id = :id', ['id' => $job['order_id']]);
                    }
                }
            }

            $this->flash('success', 'Status pekerjaan produksi berhasil diperbarui.');
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal update status job: ' . $e->getMessage());
            $this->back();
        }
    }

    public function transferBranch(string $id): void {
        $this->requirePermission('production.transfer_branch');
        $this->validateCSRF();
        $jobId = (int) $id;

        $targetBranchId = (int) Request::post('target_branch_id');
        $notes = trim(Request::post('notes'));

        try {
            Database::update('production_jobs', [
                'target_branch_id' => $targetBranchId,
                'notes'            => "Dipindahkan pengerjaannya ke cabang lain. Catatan: {$notes}"
            ], 'id = :id', ['id' => $jobId]);

            $this->flash('success', 'Pekerjaan produksi berhasil dipindahkan ke cabang pelaksana tujuan.');
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal memindahkan cabang: ' . $e->getMessage());
            $this->back();
        }
    }

    public function logMaterial(string $id): void {
        $this->requirePermission('production.execute');
        $this->validateCSRF();
        $jobId = (int) $id;

        $materialId = (int) Request::post('material_id');
        $qty = (float) Request::post('actual_quantity');
        $scrap = (float) Request::post('scrap_quantity', 0);
        $warehouseId = (int) Request::post('warehouse_id', 1);

        try {
            $job = Database::fetchOne("SELECT * FROM production_jobs WHERE id = :id", ['id' => $jobId]);
            if (!$job) throw new Exception("Job tidak ditemukan");

            // Deduct from stock
            InventoryService::recordMovement(
                $job['target_branch_id'],
                $warehouseId,
                $materialId,
                'production_out',
                $qty + $scrap,
                0.00,
                'production_job',
                $jobId,
                "Pemakaian bahan produksi job {$job['job_number']} (Bahan: {$qty}, Scrap: {$scrap})",
                Auth::id()
            );

            Database::insert('production_materials', [
                'production_job_id' => $jobId,
                'material_id'       => $materialId,
                'warehouse_id'      => $warehouseId,
                'planned_quantity'  => $qty,
                'actual_quantity'   => $qty,
                'scrap_quantity'    => $scrap
            ]);

            $this->flash('success', 'Pemakaian bahan baku aktual berhasil dicatat dan dipotong dari stok gudang.');
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal mencatat bahan: ' . $e->getMessage());
            $this->back();
        }
    }

    public function delegateBranch(string $id): void {
        $this->transferBranch($id);
    }
}
