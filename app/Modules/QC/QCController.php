<?php
namespace App\Modules\QC;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\WhatsAppService;
use Exception;

class QCController extends Controller {
    public function index(): void {
        $this->requirePermission('qc.inspect');

        $activeBranchId = Auth::activeBranchId();

        // Jobs ready for QC inspection (completed printing jobs)
        $readyJobs = Database::fetchAll("
            SELECT pj.*, o.order_number, oi.item_name, oi.quantity as item_qty, oi.unit as item_unit,
                   oi.width_cm, oi.height_cm, oi.finishing_details, m.name as machine_name, u.name as operator_name
            FROM production_jobs pj
            JOIN orders o ON pj.order_id = o.id
            JOIN order_items oi ON pj.order_item_id = oi.id
            LEFT JOIN machines m ON pj.machine_id = m.id
            LEFT JOIN users u ON pj.operator_id = u.id
            WHERE pj.status = 'completed' AND pj.target_branch_id = :bid
            ORDER BY pj.id DESC
        ", ['bid' => $activeBranchId]);

        // QC Inspection History
        $qcLogs = Database::fetchAll("
            SELECT qc.*, pj.job_number, o.order_number, oi.item_name, u.name as inspector_name
            FROM quality_checks qc
            JOIN production_jobs pj ON qc.production_job_id = pj.id
            JOIN orders o ON pj.order_id = o.id
            JOIN order_items oi ON pj.order_item_id = oi.id
            LEFT JOIN users u ON qc.inspector_id = u.id
            WHERE pj.target_branch_id = :bid
            ORDER BY qc.id DESC LIMIT 20
        ", ['bid' => $activeBranchId]);

        // Reprints Log
        $reprints = Database::fetchAll("
            SELECT pr.*, pj.job_number as original_job_number, o.order_number, u.name as approver_name
            FROM production_reprints pr
            JOIN production_jobs pj ON pr.original_job_id = pj.id
            JOIN orders o ON pj.order_id = o.id
            LEFT JOIN users u ON pr.approved_by = u.id
            WHERE pj.target_branch_id = :bid
            ORDER BY pr.id DESC
        ", ['bid' => $activeBranchId]);

        $this->render('qc/index', [
            'title'     => 'Quality Control & Cetak Ulang (Reprint)',
            'readyJobs' => $readyJobs,
            'qcLogs'    => $qcLogs,
            'reprints'  => $reprints
        ]);
    }

    public function inspect(): void {
        $this->requirePermission('qc.inspect');
        $this->validateCSRF();

        $jobId = (int) Request::post('production_job_id');
        $status = Request::post('status', 'passed');
        $notes = trim(Request::post('defect_notes', ''));
        $checklist = Request::post('checklist', []);

        Database::beginTransaction();
        try {
            $job = Database::fetchOne("SELECT * FROM production_jobs WHERE id = :id", ['id' => $jobId]);
            if (!$job) throw new Exception("Pekerjaan produksi tidak ditemukan.");

            Database::insert('quality_checks', [
                'production_job_id'   => $jobId,
                'inspector_id'        => Auth::id(),
                'check_date'          => date('Y-m-d H:i:s'),
                'checklist_json'      => json_encode($checklist),
                'status'              => $status,
                'defect_notes'        => $notes,
                'reprint_recommended'=> ($status === 'rejected') ? 1 : 0
            ]);

            if ($status === 'passed') {
                // Check if all jobs of this order are passed
                $orderId = $job['order_id'];
                Database::update('orders', ['status' => 'ready_for_pickup'], 'id = :id', ['id' => $orderId]);
                
                // Trigger notification that order is ready
                WhatsAppService::sendOrderReady($orderId);

                $this->flash('success', "Inspeksi QC Lulus (Passed)! Pesanan kini berstatus 'Siap Diambil'.");
            } else {
                $this->flash('warning', "Pekerjaan ditandai QC: " . strtoupper($status) . ". Silakan buat instruksi cetak ulang jika diperlukan.");
            }

            Database::commit();
            $this->redirect('/qc');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal memproses inspeksi QC: ' . $e->getMessage());
            $this->back();
        }
    }

    public function reprint(): void {
        $this->requirePermission('qc.reprint');
        $this->validateCSRF();

        $origJobId = (int) Request::post('original_job_id');
        $reason = trim(Request::post('reason'));
        $defectCategory = Request::post('defect_category', 'Warna Tidak Sesuai');
        $responsibleRole = Request::post('responsible_role', 'Operator Cetak');
        $costImpact = (float) Request::post('cost_impact', 0);

        Database::beginTransaction();
        try {
            $origJob = Database::fetchOne("SELECT * FROM production_jobs WHERE id = :id", ['id' => $origJobId]);
            if (!$origJob) throw new Exception("Job asal tidak ditemukan.");

            // Create new reprint job linked to the original
            $newJobNumber = $origJob['job_number'] . '-R1';
            $newJobId = Database::insert('production_jobs', [
                'company_id'       => $origJob['company_id'],
                'source_branch_id' => $origJob['source_branch_id'],
                'target_branch_id' => $origJob['target_branch_id'],
                'order_id'         => $origJob['order_id'],
                'order_item_id'    => $origJob['order_item_id'],
                'job_number'       => $newJobNumber,
                'priority'         => 'urgent',
                'status'           => 'queued',
                'scheduled_date'   => date('Y-m-d'),
                'notes'            => "Cetak Ulang (Reprint) dari {$origJob['job_number']}. Alasan: {$reason}"
            ]);

            // Insert reprint audit record
            Database::insert('production_reprints', [
                'original_job_id'  => $origJobId,
                'new_job_id'       => $newJobId,
                'reason'           => $reason,
                'defect_category'  => $defectCategory,
                'responsible_role' => $responsibleRole,
                'cost_impact'      => $costImpact,
                'approved_by'      => Auth::id()
            ]);

            // Revert order status to in_production
            Database::update('orders', ['status' => 'in_production'], 'id = :id', ['id' => $origJob['order_id']]);

            Database::commit();
            $this->flash('success', "Job Cetak Ulang '{$newJobNumber}' berhasil dibuat dan dimasukkan ke antrean prioritas.");
            $this->redirect('/qc');
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal memproses cetak ulang: ' . $e->getMessage());
            $this->back();
        }
    }
}
