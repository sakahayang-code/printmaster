<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Antrean & Papan Produksi</h3>
        <p class="text-muted small mb-0">Kelola jadwal mesin, alokasi operator, tahapan pengerjaan, dan pencatatan bahan baku aktual.</p>
    </div>
    <div class="d-flex gap-2">
        <ul class="nav nav-pills" id="prodViewToggle">
            <li class="nav-item">
                <button class="nav-link active btn-sm py-1 px-3 fw-semibold" data-bs-toggle="pill" data-bs-target="#viewKanban">
                    <i class="bi bi-kanban me-1"></i> Kanban Board
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link btn-sm py-1 px-3 fw-semibold" data-bs-toggle="pill" data-bs-target="#viewList">
                    <i class="bi bi-list-task me-1"></i> Daftar Tabel
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
    <!-- KANBAN BOARD VIEW -->
    <div class="tab-pane fade show active" id="viewKanban">
        <div class="row g-3">
            <!-- KOLOM 1: ANTREAN (QUEUED) -->
            <div class="col-md-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-header bg-secondary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase"><i class="bi bi-clock me-1"></i> Antrean Mesin</span>
                        <span class="badge bg-white text-secondary"><?= count($kanban['queued']) ?></span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 500px;">
                        <?php foreach ($kanban['queued'] as $jb): ?>
                        <div class="card border p-3 shadow-sm bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-primary small"><?= htmlspecialchars($jb['job_number']) ?></span>
                                <?php if ($jb['priority'] === 'urgent'): ?>
                                    <span class="badge bg-danger">Urgent</span>
                                <?php elseif ($jb['priority'] === 'high'): ?>
                                    <span class="badge bg-warning text-dark">High</span>
                                <?php endif; ?>
                            </div>
                            <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($jb['item_name']) ?></div>
                            <div class="text-xs text-muted mb-2">Order: <?= htmlspecialchars($jb['order_number']) ?> • Qty: <?= $jb['item_qty'] ?> <?= $jb['item_unit'] ?></div>
                            <?php if (!empty($jb['finishing_details'])): ?>
                            <div class="small text-danger mb-2"><i class="bi bi-scissors me-1"></i><?= htmlspecialchars($jb['finishing_details']) ?></div>
                            <?php endif; ?>

                            <div class="pt-2 border-top small text-muted d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cpu me-1"></i><?= htmlspecialchars($jb['machine_name'] ?? 'Pilih Mesin') ?></span>
                                <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="openAssignModal(<?= $jb['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>

                            <div class="d-flex gap-1 mt-2">
                                <form action="/production/jobs/<?= $jb['id'] ?>/status" method="POST" class="w-100">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-sm btn-primary w-100 py-1 fw-semibold">
                                        <i class="bi bi-play-fill me-1"></i> Mulai Cetak
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-warning py-1" title="Pindah Cabang" onclick="openTransferModal(<?= $jb['id'] ?>)">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- KOLOM 2: SEDANG PROSES (IN PROGRESS) -->
            <div class="col-md-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase"><i class="bi bi-play-circle me-1"></i> Sedang Cetak</span>
                        <span class="badge bg-white text-primary"><?= count($kanban['in_progress']) ?></span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 500px;">
                        <?php foreach ($kanban['in_progress'] as $jb): ?>
                        <div class="card border border-primary p-3 shadow-sm bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-primary small"><?= htmlspecialchars($jb['job_number']) ?></span>
                                <span class="badge bg-primary">Berjalan</span>
                            </div>
                            <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($jb['item_name']) ?></div>
                            <div class="text-xs text-muted mb-2">Order: <?= htmlspecialchars($jb['order_number']) ?></div>

                            <div class="p-2 bg-light rounded small mb-2">
                                <div><i class="bi bi-cpu me-1"></i><strong><?= htmlspecialchars($jb['machine_name'] ?? '-') ?></strong></div>
                                <div><i class="bi bi-person me-1"></i>Op: <?= htmlspecialchars($jb['operator_name'] ?? '-') ?></div>
                            </div>

                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-info w-50 py-1" onclick="openMaterialModal(<?= $jb['id'] ?>)">
                                    <i class="bi bi-box me-1"></i> Catat Bahan
                                </button>
                                <form action="/production/jobs/<?= $jb['id'] ?>/status" method="POST" class="w-50">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="status" value="finishing">
                                    <button type="submit" class="btn btn-sm btn-warning w-100 py-1 fw-semibold text-dark">
                                        Finishing <i class="bi bi-arrow-right"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- KOLOM 3: TAHAP FINISHING -->
            <div class="col-md-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-dark py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase"><i class="bi bi-scissors me-1"></i> Finishing & Packing</span>
                        <span class="badge bg-dark text-white"><?= count($kanban['finishing']) ?></span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 500px;">
                        <?php foreach ($kanban['finishing'] as $jb): ?>
                        <div class="card border p-3 shadow-sm bg-white">
                            <div class="fw-bold text-primary small mb-1"><?= htmlspecialchars($jb['job_number']) ?></div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($jb['item_name']) ?></div>
                            <div class="small text-danger my-1 fw-semibold"><?= htmlspecialchars($jb['finishing_details'] ?? 'Pemotongan / Packing') ?></div>

                            <form action="/production/jobs/<?= $jb['id'] ?>/status" method="POST" class="mt-2">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-sm btn-success w-100 py-1 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Selesai Cetak
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- KOLOM 4: SELESAI / SIAP QC -->
            <div class="col-md-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-uppercase"><i class="bi bi-shield-check me-1"></i> Siap QC / Selesai</span>
                        <span class="badge bg-white text-success"><?= count($kanban['completed']) ?></span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 500px;">
                        <?php foreach ($kanban['completed'] as $jb): ?>
                        <div class="card border p-3 shadow-sm bg-white border-success">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-success small"><?= htmlspecialchars($jb['job_number']) ?></span>
                                <span class="badge bg-success"><i class="bi bi-check2"></i> Selesai</span>
                            </div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($jb['item_name']) ?></div>
                            <div class="text-xs text-muted mb-2">Order: <?= htmlspecialchars($jb['order_number']) ?></div>
                            <a href="/qc" class="btn btn-sm btn-outline-success w-100 py-1">
                                <i class="bi bi-shield-check me-1"></i> Periksa di QC
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LIST TABLE VIEW -->
    <div class="tab-pane fade" id="viewList">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Job Produksi</th>
                                <th>No. Order</th>
                                <th>Item & Spesifikasi</th>
                                <th>Mesin</th>
                                <th>Operator</th>
                                <th>Prioritas</th>
                                <th>Status Job</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $jb): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($jb['job_number']) ?></td>
                                <td><a href="/orders/<?= $jb['order_id'] ?>"><?= htmlspecialchars($jb['order_number']) ?></a></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($jb['item_name']) ?></div>
                                    <small class="text-muted"><?= $jb['item_qty'] ?> <?= $jb['item_unit'] ?> • <?= htmlspecialchars($jb['finishing_details'] ?? '-') ?></small>
                                </td>
                                <td><?= htmlspecialchars($jb['machine_name'] ?? 'Belum dipilih') ?></td>
                                <td><?= htmlspecialchars($jb['operator_name'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= strtoupper($jb['priority']) ?></span></td>
                                <td><?= FormatHelper::badgeStatusJob($jb['status']) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="openAssignModal(<?= $jb['id'] ?>)">
                                        <i class="bi bi-sliders"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ASSIGN MESIN & OPERATOR -->
<div class="modal fade" id="modalAssignJob" tabindex="-1">
    <div class="modal-dialog">
        <form id="formAssignJob" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Alokasi Mesin & Operator</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Mesin Produksi</label>
                    <select name="machine_id" class="form-select">
                        <option value="">-- Belum Ditentukan --</option>
                        <?php foreach ($machines as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?> (<?= $m['machine_type'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tugaskan Operator</label>
                    <select name="operator_id" class="form-select">
                        <option value="">-- Belum Ditugaskan --</option>
                        <?php foreach ($operators as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= htmlspecialchars($op['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tingkat Prioritas</label>
                    <select name="priority" class="form-select">
                        <option value="normal">Normal</option>
                        <option value="high">High (Segera)</option>
                        <option value="urgent">Urgent / Kilat (Prioritas Utama)</option>
                        <option value="low">Low (Santai)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TRANSFER JOB CABANG -->
<div class="modal fade" id="modalTransferJob" tabindex="-1">
    <div class="modal-dialog">
        <form id="formTransferJob" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Pindahkan Pekerjaan Antar-Cabang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Pindahkan pelaksanaan job cetak ini ke cabang lain bila mesin cabang saat ini sedang padat atau dalam perbaikan.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Cabang Pelaksana Tujuan</label>
                    <select name="target_branch_id" class="form-select" required>
                        <?php foreach ($branches as $br): ?>
                        <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?> (<?= htmlspecialchars($br['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Pemindahan / Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Kelebihan beban mesin cabang asal..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning fw-bold">Konfirmasi Pemindahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CATAT BAHAN AKTUAL -->
<div class="modal fade" id="modalMaterialJob" tabindex="-1">
    <div class="modal-dialog">
        <form id="formMaterialJob" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-box me-2"></i>Catat Pemakaian Bahan Baku Aktual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Bahan Baku yang Dipakai</label>
                    <select name="material_id" class="form-select" required>
                        <?php foreach ($materials as $mat): ?>
                        <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['name']) ?> (Stok: <?= $mat['current_stock'] ?> <?= $mat['unit'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Jumlah Terpakai</label>
                        <input type="number" name="actual_quantity" class="form-control" step="0.01" min="0.01" required placeholder="Contoh: 6">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Scrap / Rusak (Opsional)</label>
                        <input type="number" name="scrap_quantity" class="form-control" step="0.01" min="0" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info fw-bold">Potong dari Stok Gudang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(id) {
    document.getElementById('formAssignJob').action = '/production/jobs/' + id + '/assign';
    new bootstrap.Modal(document.getElementById('modalAssignJob')).show();
}

function openTransferModal(id) {
    document.getElementById('formTransferJob').action = '/production/jobs/' + id + '/transfer-branch';
    new bootstrap.Modal(document.getElementById('modalTransferJob')).show();
}

function openMaterialModal(id) {
    document.getElementById('formMaterialJob').action = '/production/jobs/' + id + '/materials';
    new bootstrap.Modal(document.getElementById('modalMaterialJob')).show();
}
</script>
