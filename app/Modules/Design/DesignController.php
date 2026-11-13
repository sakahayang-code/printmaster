<?php
namespace App\Modules\Design;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use Exception;

class DesignController extends Controller {
    public function index(): void {
        $this->requirePermission('design.view');

        $activeBranchId = Auth::activeBranchId();

        // Orders needing design or having design files
        $orders = Database::fetchAll("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone,
                   (SELECT COUNT(*) FROM order_files WHERE order_id = o.id) as total_files,
                   (SELECT COUNT(*) FROM design_revisions WHERE order_id = o.id) as total_revisions
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.branch_id = :bid AND o.status != 'cancelled'
            ORDER BY o.id DESC
        ", ['bid' => $activeBranchId]);

        $files = Database::fetchAll("
            SELECT f.*, o.order_number, c.name as customer_name, u.name as uploader_name
            FROM order_files f
            JOIN orders o ON f.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN users u ON f.uploaded_by = u.id
            WHERE o.branch_id = :bid
            ORDER BY f.id DESC
        ", ['bid' => $activeBranchId]);

        $revisions = Database::fetchAll("
            SELECT dr.*, o.order_number, u.name as designer_name
            FROM design_revisions dr
            JOIN orders o ON dr.order_id = o.id
            LEFT JOIN users u ON dr.designer_id = u.id
            WHERE o.branch_id = :bid
            ORDER BY dr.id DESC
        ", ['bid' => $activeBranchId]);

        $this->render('design/index', [
            'title'     => 'Manajemen Desain & Proofing',
            'orders'    => $orders,
            'files'     => $files,
            'revisions' => $revisions
        ]);
    }

    public function upload(): void {
        $this->requirePermission('design.upload');
        $this->validateCSRF();

        $orderId = (int) Request::post('order_id');
        $file = Request::file('design_file');
        $remarks = trim(Request::post('remarks'));
        $resDpi = (int) Request::post('resolution_dpi', 300);
        $colorMode = Request::post('color_mode', 'CMYK');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Silakan pilih file desain yang valid.');
            $this->back();
        }

        // Whitelist MIME and extensions
        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'zip', 'rar', 'ai', 'cdr', 'psd', 'tiff'];

        if (!in_array($ext, $allowedExts)) {
            $this->flash('error', "Format file .{$ext} tidak diizinkan. Gunakan PDF, JPG, PNG, AI, CDR, PSD, atau ZIP.");
            $this->back();
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/designs';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = 'DSN-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        $destPath = $uploadDir . '/' . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            Database::insert('order_files', [
                'order_id'          => $orderId,
                'original_filename' => $origName,
                'stored_filename'   => $safeName,
                'file_path'         => $destPath,
                'file_size'         => $file['size'],
                'mime_type'         => $file['type'],
                'resolution_dpi'    => $resDpi,
                'color_mode'        => $colorMode,
                'remarks'           => $remarks,
                'uploaded_by'       => Auth::id()
            ]);

            // Update order status if it was awaiting_design
            Database::query("UPDATE orders SET status = 'design_in_progress' WHERE id = :id AND status = 'awaiting_design'", ['id' => $orderId]);

            $this->flash('success', "File desain '{$origName}' berhasil diunggah.");
        } else {
            $this->flash('error', 'Gagal memindahkan file yang diunggah.');
        }

        $this->back();
    }

    public function createProof(): void {
        $this->requirePermission('design.upload');
        $this->validateCSRF();

        $orderId = (int) Request::post('order_id');
        $notes = trim(Request::post('notes'));
        $file = Request::file('proof_image');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'File gambar proof wajib diunggah.');
            $this->back();
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/proofs';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = 'PROOF-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        $destPath = $uploadDir . '/' . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $version = (int) Database::fetchColumn("SELECT COALESCE(MAX(version_number), 0) + 1 FROM design_revisions WHERE order_id = :oid", ['oid' => $orderId]);

            Database::insert('design_revisions', [
                'order_id'         => $orderId,
                'version_number'   => $version,
                'proof_image_path' => '/storage/uploads/proofs/' . $safeName,
                'designer_id'      => Auth::id(),
                'notes'            => $notes,
                'status'           => 'submitted'
            ]);

            // Update order status
            Database::update('orders', ['status' => 'awaiting_design_approval'], 'id = :id', ['id' => $orderId]);

            $this->flash('success', "Draft proof revisi ke-{$version} berhasil disimpan dan dikirim untuk persetujuan pelanggan.");
        } else {
            $this->flash('error', 'Gagal mengunggah file proof.');
        }

        $this->back();
    }

    public function approveProof(string $id): void {
        $this->requirePermission('design.approve');
        $this->validateCSRF();
        $revId = (int) $id;

        $rev = Database::fetchOne("SELECT * FROM design_revisions WHERE id = :id", ['id' => $revId]);
        if (!$rev) {
            $this->flash('error', 'Data proof tidak ditemukan.');
            $this->back();
        }

        Database::beginTransaction();
        try {
            Database::update('design_revisions', [
                'status'            => 'approved',
                'customer_feedback' => Request::post('feedback', 'Desain disetujui (ACC Cetak)'),
                'reviewed_at'       => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $revId]);

            // Lock design and move order to queued
            Database::update('orders', ['status' => 'queued'], 'id = :id', ['id' => $rev['order_id']]);

            Database::commit();
            $this->flash('success', "Proof desain berhasil disetujui (ACC Cetak)! Order siap diproduksi.");
        } catch (Exception $e) {
            Database::rollBack();
            $this->flash('error', 'Gagal menyetujui proof: ' . $e->getMessage());
        }

        $this->back();
    }
}
