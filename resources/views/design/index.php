<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Manajemen Desain & Proofing</h3>
        <p class="text-muted small mb-0">Antrean pra-cetak (prepress), pemeriksaan resolusi file, pembuatan draft proofing, dan approval pelanggan.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadFile">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload File Desain
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateProof">
            <i class="bi bi-images me-1"></i> Kirim Proof Revisi ke Pelanggan
        </button>
    </div>
</div>

<ul class="nav nav-pills mb-3" id="designTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="antrean-tab" data-bs-toggle="pill" data-bs-target="#tab-antrean">
            <i class="bi bi-brush me-1"></i> Antrean Desain & Order (<?= count($orders) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="proofs-tab" data-bs-toggle="pill" data-bs-target="#tab-proofs">
            <i class="bi bi-check2-circle me-1"></i> Draft Proof & Revisi (<?= count($revisions) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="files-tab" data-bs-toggle="pill" data-bs-target="#tab-files">
            <i class="bi bi-files me-1"></i> Berkas File Master (<?= count($files) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: ANTREAN ORDER DESAIN -->
    <div class="tab-pane fade show active" id="tab-antrean">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Order</th>
                                <th>Pelanggan</th>
                                <th>Status Alur</th>
                                <th>Total File</th>
                                <th>Revisi Proof</th>
                                <th>Tenggat</th>
                                <th class="text-end">Aksi Desainer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <a href="/orders/<?= $o['id'] ?>" class="fw-bold text-decoration-none">
                                        <?= htmlspecialchars($o['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($o['customer_phone']) ?></small>
                                </td>
                                <td><?= FormatHelper::badgeStatusOrder($o['status']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $o['total_files'] ?> File</span></td>
                                <td><span class="badge bg-secondary">Versi <?= $o['total_revisions'] ?></span></td>
                                <td><?= FormatHelper::tanggal($o['deadline_date']) ?></td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="quickUpload(<?= $o['id'] ?>, '<?= htmlspecialchars($o['order_number']) ?>')">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="quickProof(<?= $o['id'] ?>, '<?= htmlspecialchars($o['order_number']) ?>')">
                                            <i class="bi bi-check2"></i> Buat Proof
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: DRAFT PROOF REVISI -->
    <div class="tab-pane fade" id="tab-proofs">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Order</th>
                                <th>Versi Proof</th>
                                <th>Desainer</th>
                                <th>Catatan Desainer</th>
                                <th>Status ACC</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-end">Aksi Approval</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($revisions)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada proof revisi.</td></tr>
                            <?php else: ?>
                                <?php foreach ($revisions as $rev): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($rev['order_number']) ?></td>
                                    <td><span class="badge bg-dark">Versi #<?= $rev['version_number'] ?></span></td>
                                    <td><?= htmlspecialchars($rev['designer_name'] ?? 'Desainer') ?></td>
                                    <td><?= htmlspecialchars($rev['notes'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($rev['status'] === 'approved'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>ACC Cetak</span>
                                        <?php elseif ($rev['status'] === 'submitted'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu Respon</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($rev['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= FormatHelper::tanggal($rev['created_at']) ?></td>
                                    <td class="text-end">
                                        <?php if ($rev['status'] !== 'approved'): ?>
                                        <form action="/designs/<?= $rev['id'] ?>/approve" method="POST" class="d-inline" onsubmit="return confirm('Setujui proof desain ini sebagai acuan cetak produksi?')">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="feedback" value="Desain disetujui (ACC Cetak)">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                <i class="bi bi-check-lg me-1"></i> ACC Cetak
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: FILE MASTER -->
    <div class="tab-pane fade" id="tab-files">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Nama File</th>
                                <th>Order & Pelanggan</th>
                                <th>Ukuran File</th>
                                <th>DPI & Mode</th>
                                <th>Pengunggah</th>
                                <th>Tanggal Upload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($files)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada file master yang tersimpan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($files as $fl): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><i class="bi bi-file-earmark-check text-primary me-1"></i><?= htmlspecialchars($fl['original_filename']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($fl['remarks'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($fl['order_number']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($fl['customer_name']) ?></small>
                                    </td>
                                    <td><?= round($fl['file_size'] / 1024, 1) ?> KB</td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= $fl['resolution_dpi'] ?> DPI</span>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($fl['color_mode']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($fl['uploader_name'] ?? 'Pelanggan') ?></td>
                                    <td><?= FormatHelper::tanggal($fl['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD FILE DESAIN -->
<div class="modal fade" id="modalUploadFile" tabindex="-1">
    <div class="modal-dialog">
        <form action="/designs/upload" method="POST" enctype="multipart/form-data" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Upload File Desain Pelanggan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Pesanan (Order) <span class="text-danger">*</span></label>
                    <select name="order_id" id="uploadOrderId" class="form-select" required>
                        <?php foreach ($orders as $ord): ?>
                        <option value="<?= $ord['id'] ?>"><?= htmlspecialchars($ord['order_number']) ?> - <?= htmlspecialchars($ord['customer_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih File Desain (PDF / CDR / AI / PSD / JPG / PNG / ZIP) <span class="text-danger">*</span></label>
                    <input type="file" name="design_file" class="form-control" required>
                    <small class="text-muted">Maksimal ukuran file: 25 MB.</small>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Resolusi File</label>
                        <select name="resolution_dpi" class="form-select">
                            <option value="300" selected>300 DPI (Standar Cetak Tajam)</option>
                            <option value="150">150 DPI (Banner Luar Ruangan)</option>
                            <option value="72">72 DPI (Draft / Screen)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Mode Warna</label>
                        <select name="color_mode" class="form-select">
                            <option value="CMYK" selected>CMYK (Percetakan)</option>
                            <option value="RGB">RGB (Tampilan Digital)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Prepress / Font / Bleed</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Contoh: Sudah convert to curves, bleed 2mm">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Unggah File</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL BUAT PROOF REVISI -->
<div class="modal fade" id="modalCreateProof" tabindex="-1">
    <div class="modal-dialog">
        <form action="/designs/create-proof" method="POST" enctype="multipart/form-data" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Kirim Draft Proofing Desain ke Pelanggan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Pesanan (Order) <span class="text-danger">*</span></label>
                    <select name="order_id" id="proofOrderId" class="form-select" required>
                        <?php foreach ($orders as $ord): ?>
                        <option value="<?= $ord['id'] ?>"><?= htmlspecialchars($ord['order_number']) ?> - <?= htmlspecialchars($ord['customer_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Upload Gambar Mockup / Proof (JPG/PNG) <span class="text-danger">*</span></label>
                    <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Revisi / Instruksi untuk Pelanggan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Mohon periksa kembali ejaan nama, nomor telepon, dan tata letak sebelum kami cetak massal."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold">Kirim Proof</button>
            </div>
        </form>
    </div>
</div>

<script>
function quickUpload(id, num) {
    document.getElementById('uploadOrderId').value = id;
    new bootstrap.Modal(document.getElementById('modalUploadFile')).show();
}

function quickProof(id, num) {
    document.getElementById('proofOrderId').value = id;
    new bootstrap.Modal(document.getElementById('modalCreateProof')).show();
}
</script>
