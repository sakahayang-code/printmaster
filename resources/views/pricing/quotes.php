<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Daftar Penawaran Harga (Quotes)</h3>
        <p class="text-muted small mb-0">Kelola estimasi dan penawaran resmi untuk calon pelanggan sebelum dikonversi menjadi order.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/pricing" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calculator me-1"></i> Kalkulator Harga</a>
        <a href="/quotes/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Buat Penawaran Baru</a>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>No. Penawaran</th>
                        <th>Pelanggan</th>
                        <th>Cabang</th>
                        <th>Item</th>
                        <th>Total Penawaran</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotes)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text fs-2 d-block mb-2 text-secondary"></i>
                            Belum ada surat penawaran harga yang dibuat.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($quotes as $q): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($q['quote_number']) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($q['customer_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($q['customer_phone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($q['branch_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= $q['total_items'] ?> Item</span></td>
                            <td class="fw-bold text-dark"><?= FormatHelper::rupiah($q['total']) ?></td>
                            <td><?= FormatHelper::tanggal($q['valid_until'], false) ?></td>
                            <td>
                                <?php if ($q['status'] === 'converted'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Dikonversi ke Order</span>
                                <?php elseif ($q['status'] === 'sent'): ?>
                                    <span class="badge bg-primary">Terkirim ke Pelanggan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($q['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($q['status'] !== 'converted'): ?>
                                <form action="/quotes/<?= $q['id'] ?>/convert-order" method="POST" class="d-inline" onsubmit="return confirm('Konversi penawaran ini menjadi pesanan riil?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                        <i class="bi bi-arrow-right-circle me-1"></i> Jadi Order
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="badge bg-light text-secondary border">Selesai</span>
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
