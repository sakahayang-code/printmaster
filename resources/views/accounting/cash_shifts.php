<?php
use App\Helpers\FormatHelper;
use App\Core\Auth;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Shift Kasir & Tutup Kasir</h4>
        <p class="text-muted small mb-0">Manajemen buka/tutup kasir per register, rekonsiliasi uang kas, dan audit selisih kas.</p>
    </div>
    <div class="col-auto">
        <?php if ($myOpenShift): ?>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
            <i class="bi bi-door-closed me-1"></i> Tutup Shift Saya
        </button>
        <?php else: ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#openShiftModal">
            <i class="bi bi-door-open me-1"></i> Buka Shift Baru
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($myOpenShift): ?>
<div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 p-3 mb-4 rounded-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-cash-coin fs-4"></i>
            </div>
            <div>
                <span class="badge bg-success mb-1">Shift Aktif</span>
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($myOpenShift['register_name']) ?></h5>
                <small class="text-muted">Dibuka: <?= FormatHelper::datetime($myOpenShift['opening_time']) ?> | Modal Awal: <strong><?= FormatHelper::rupiah($myOpenShift['opening_balance']) ?></strong></small>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
            Tutup Shift Sekarang
        </button>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0">
        <h6 class="fw-bold mb-0">Riwayat Shift Kasir</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID Shift</th>
                    <th>Cabang & Register</th>
                    <th>Kasir</th>
                    <th>Waktu Buka / Tutup</th>
                    <th class="text-end">Modal Awal</th>
                    <th class="text-end">Penjualan Kas</th>
                    <th class="text-end">Total Fisik</th>
                    <th class="text-center">Selisih</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shifts)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada data shift kasir.</td>
                </tr>
                <?php else: foreach ($shifts as $s): ?>
                <tr>
                    <td><strong>#<?= $s['id'] ?></strong></td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($s['branch_name']) ?></span>
                        <div class="fw-semibold small mt-1"><?= htmlspecialchars($s['register_name']) ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($s['cashier_name']) ?></div>
                    </td>
                    <td class="small">
                        <div><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= FormatHelper::datetime($s['opening_time']) ?></div>
                        <?php if ($s['closing_time']): ?>
                        <div class="text-muted"><i class="bi bi-box-arrow-left text-danger me-1"></i><?= FormatHelper::datetime($s['closing_time']) ?></div>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark">Masih Berlangsung</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= FormatHelper::rupiah($s['opening_balance']) ?></td>
                    <td class="text-end fw-semibold text-success"><?= FormatHelper::rupiah($s['cash_sales']) ?></td>
                    <td class="text-end fw-bold"><?= FormatHelper::rupiah($s['actual_balance']) ?></td>
                    <td class="text-center">
                        <?php if ($s['difference'] == 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Pas (Rp 0)</span>
                        <?php elseif ($s['difference'] > 0): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">+<?= FormatHelper::rupiah($s['difference']) ?></span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= FormatHelper::rupiah($s['difference']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($s['status'] === 'open'): ?>
                        <span class="badge bg-success">Open</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Closed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buka Shift -->
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/cash-shifts/open" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-door-open me-2 text-primary"></i>Buka Shift Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Mesin Register / Laci Kas</label>
                    <select name="cash_register_id" class="form-select" required>
                        <?php foreach ($registers as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Modal Kas Awal (Opening Cash)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="opening_balance" class="form-control" placeholder="Contoh: 200000" min="0" step="1000" required>
                    </div>
                    <small class="text-muted">Uang kembalian yang disiapkan di awal shift.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Buka Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tutup Shift -->
<?php if ($myOpenShift): ?>
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/cash-shifts/close" class="modal-content">
            <?= CSRF::field() ?>
            <input type="hidden" name="shift_id" value="<?= $myOpenShift['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-door-closed me-2 text-danger"></i>Tutup Shift Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    Hitung seluruh uang fisik di laci kas secara manual sebelum menginput nominal aktual.
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Modal Awal</label>
                    <div class="fw-bold fs-6"><?= FormatHelper::rupiah($myOpenShift['opening_balance']) ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Uang Kas Fisik Aktual di Laci</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="actual_balance" class="form-control" placeholder="0" min="0" step="1000" required>
                    </div>
                    <small class="text-muted">Total uang tunai hasil hitungan fisik saat ini.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan Penutupan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan bila ada selisih uang..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-lock me-1"></i> Tutup & Kunci Shift</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
