<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-buildings me-2 text-primary"></i>Manajemen Perusahaan & Multi-Cabang</h4>
        <p class="text-muted small mb-0">Kelola cabang operasional percetakan, jam operasional, target omset bulanan, dan isolasi data per cabang.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBranchModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Cabang Baru
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($branches as $b): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?= $b['is_headquarters'] ? 'border-primary border-2' : '' ?>">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary font-monospace"><?= htmlspecialchars($b['code']) ?></span>
                        <?php if ($b['is_headquarters']): ?>
                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-star-fill me-1"></i>Pusat (HQ)</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($b['is_active']): ?>
                    <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                    <span class="badge bg-secondary">Nonaktif</span>
                    <?php endif; ?>
                </div>

                <h5 class="fw-bold mb-1"><?= htmlspecialchars($b['name']) ?></h5>
                <p class="text-muted small mb-3 flex-grow-1"><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($b['address'] ?? 'Alamat belum diatur') ?></p>

                <div class="bg-light p-3 rounded-3 mb-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><i class="bi bi-people me-1"></i> Staf Karyawan:</span>
                        <strong><?= $b['total_users'] ?> orang</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><i class="bi bi-printer me-1"></i> Mesin Aktif:</span>
                        <strong><?= $b['total_machines'] ?> unit</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><i class="bi bi-box me-1"></i> Gudang Bahan:</span>
                        <strong><?= $b['total_warehouses'] ?> gudang</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-1 border-top">
                        <span class="text-muted">Target Bulanan:</span>
                        <strong class="text-success"><?= FormatHelper::rupiah($b['monthly_target']) ?></strong>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($b['operating_hours'] ?? '08:00 - 21:00') ?></small>
                    <?php if (!$b['is_headquarters']): ?>
                    <form method="POST" action="/branches/<?= $b['id'] ?>/toggle" class="d-inline">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-sm <?= $b['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                            <?= $b['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah Cabang -->
<div class="modal fade" id="newBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/branches/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2 text-primary"></i>Tambah Cabang Percetakan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode Cabang</label>
                        <input type="text" name="code" class="form-control font-monospace text-uppercase" placeholder="SMG, YOG" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Cabang</label>
                        <input type="text" name="name" class="form-control" placeholder="Cabang Semarang Pemuda" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap Cabang</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Jl. Pemuda No. 100, Semarang..." required></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" placeholder="024-87654321 / 0812..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jam Operasional</label>
                        <input type="text" name="operating_hours" class="form-control" value="08:00 - 21:00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Target Omset Bulanan (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="monthly_target" class="form-control" placeholder="50000000" min="0" step="1000000" value="50000000">
                    </div>
                </div>

                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Gudang bahan baku utama dan mesin register kasir untuk cabang ini akan otomatis dibuatkan oleh sistem.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Daftarkan Cabang</button>
            </div>
        </form>
    </div>
</div>
