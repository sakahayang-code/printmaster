<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-tag me-2 text-primary"></i>Voucher Promo & Diskon Pemasaran</h4>
        <p class="text-muted small mb-0">Kelola kupon diskon persentase (%) atau nominal tetap (Rp), kuota pemakaian, dan masa berlaku promo.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPromoModal">
            <i class="bi bi-plus-lg me-1"></i> Buat Voucher Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Voucher</th>
                    <th>Nama Program Promo</th>
                    <th>Diskon</th>
                    <th>Syarat Belanja</th>
                    <th class="text-center">Kuota & Terpakai</th>
                    <th>Masa Berlaku</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promotions)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data voucher promo.</td>
                </tr>
                <?php else: foreach ($promotions as $p): ?>
                <tr>
                    <td>
                        <span class="badge bg-primary fs-6 font-monospace px-2 py-1"><?= htmlspecialchars($p['code']) ?></span>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div>
                    </td>
                    <td>
                        <?php if ($p['discount_type'] === 'percentage'): ?>
                        <span class="fw-bold text-success fs-6"><?= $p['discount_value'] ?>%</span>
                        <?php if ($p['max_discount'] > 0): ?>
                        <div class="small text-muted">Maks. <?= FormatHelper::rupiah($p['max_discount']) ?></div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="fw-bold text-success fs-6"><?= FormatHelper::rupiah($p['discount_value']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <?php if ($p['min_purchase'] > 0): ?>
                        <div>Min. Belanja: <strong><?= FormatHelper::rupiah($p['min_purchase']) ?></strong></div>
                        <?php else: ?>
                        <span class="text-muted">Tanpa minimum belanja</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="fw-bold small"><?= $p['used_count'] ?> / <?= $p['quota'] ?></div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-info" style="width: <?= min(100, ($p['used_count'] / max(1, $p['quota'])) * 100) ?>%;"></div>
                        </div>
                    </td>
                    <td class="small">
                        <div><?= FormatHelper::date($p['start_date']) ?> s/d</div>
                        <div class="text-muted"><?= FormatHelper::date($p['end_date']) ?></div>
                    </td>
                    <td class="text-center">
                        <?php if ($p['is_active']): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="/promotions/<?= $p['id'] ?>/toggle" class="btn btn-sm <?= $p['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Ubah Status">
                            <?= $p['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buat Voucher Baru -->
<div class="modal fade" id="newPromoModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/promotions/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-tag me-2 text-primary"></i>Buat Voucher Diskon Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kode Voucher (Kupon)</label>
                        <input type="text" name="code" class="form-control font-monospace text-uppercase" placeholder="DISKON10, MERDEKA50" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jenis Diskon</label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama / Judul Promo</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Promo Banner Pilkada 10%" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nilai Diskon</label>
                        <input type="number" name="discount_value" class="form-control" placeholder="10 (untuk 10%) atau 50000" min="1" step="any" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Maksimal Diskon (Rp)</label>
                        <input type="number" name="max_discount" class="form-control" placeholder="0 jika tanpa batas" min="0" step="1000" value="0">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Min. Pembelian (Rp)</label>
                        <input type="number" name="min_purchase" class="form-control" placeholder="0" min="0" step="5000" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kuota Penggunaan</label>
                        <input type="number" name="quota" class="form-control" placeholder="100" min="1" value="100" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Berakhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan & Terbitkan Voucher</button>
            </div>
        </form>
    </div>
</div>
