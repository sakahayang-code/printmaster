<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-activity me-2 text-primary"></i>Audit Trail & Cadangan Database (Backup)</h4>
        <p class="text-muted small mb-0">Catatan riwayat perubahan data sensitif, aksi pengguna (login, edit harga, hapus, override), dan ekspor backup SQL.</p>
    </div>
    <div class="col-auto">
        <a href="/backup/download" class="btn btn-success">
            <i class="bi bi-cloud-arrow-down me-1"></i> Unduh Cadangan Database (.SQL)
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0">
        <h6 class="fw-bold mb-0">Rekam Jejak Aktivitas Terakhir (Audit Logs)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu Aktivitas</th>
                    <th>Pengguna</th>
                    <th>Cabang</th>
                    <th>Aksi</th>
                    <th>Entitas / Tabel</th>
                    <th>Detail Nilai Baru</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada catatan log audit.</td>
                </tr>
                <?php else: foreach ($logs as $l): ?>
                <tr>
                    <td class="small"><?= FormatHelper::datetime($l['created_at']) ?></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($l['user_name'] ?? 'System') ?></div>
                        <?php if ($l['username']): ?>
                        <div class="text-muted small font-monospace">@<?= htmlspecialchars($l['username']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($l['branch_name'] ?? 'Global') ?></span></td>
                    <td>
                        <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($l['action']) ?></span>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($l['entity']) ?></strong>
                        <?php if ($l['entity_id']): ?>
                        <span class="text-muted small">#<?= $l['entity_id'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted font-monospace" style="max-width: 250px;">
                        <?= htmlspecialchars(substr($l['new_values'] ?? '-', 0, 80)) ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
