<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack me-2 text-primary"></i>Pembayaran & Riwayat Transaksi</h4>
        <p class="text-muted small mb-0">Semua transaksi masuk, pelunasan DP pesanan, metode bayar (Tunai, Bank, QRIS, EDC), dan verifikasi.</p>
    </div>
    <div class="col-auto">
        <div class="card bg-success text-white px-3 py-2 border-0 shadow-sm rounded-3">
            <small class="opacity-75">Total Pembayaran Terverifikasi</small>
            <div class="fw-bold fs-5"><?= FormatHelper::rupiah($totalPaid) ?></div>
        </div>
    </div>
</div>

<!-- Filter card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/payments" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Metode Pembayaran</label>
                <select name="method" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Metode</option>
                    <option value="cash" <?= $method === 'cash' ? 'selected' : '' ?>>Tunai (Cash)</option>
                    <option value="bank_transfer" <?= $method === 'bank_transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                    <option value="qris" <?= $method === 'qris' ? 'selected' : '' ?>>QRIS</option>
                    <option value="edc" <?= $method === 'edc' ? 'selected' : '' ?>>EDC / Debit / Kartu Kredit</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Status Verifikasi</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified (Sah)</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending (Menunggu Verifikasi)</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="/payments" class="btn btn-sm btn-outline-secondary w-100">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Bukti Bayar</th>
                    <th>No. Pesanan & Pelanggan</th>
                    <th>Cabang</th>
                    <th>Waktu Pembayaran</th>
                    <th>Metode</th>
                    <th class="text-end">Jumlah Bayar</th>
                    <th class="text-center">Status</th>
                    <th>Kasir</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat pembayaran yang cocok dengan filter.</td>
                </tr>
                <?php else: foreach ($payments as $p): ?>
                <tr>
                    <td>
                        <strong class="text-primary"><?= htmlspecialchars($p['payment_number']) ?></strong>
                        <?php if ($p['reference_number']): ?>
                        <div class="small text-muted">Ref: <?= htmlspecialchars($p['reference_number']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['order_number']): ?>
                        <a href="/orders/<?= $p['order_id'] ?>" class="fw-semibold text-decoration-none">
                            <?= htmlspecialchars($p['order_number']) ?>
                        </a>
                        <div class="small text-muted"><?= htmlspecialchars($p['customer_name'] ?? '-') ?></div>
                        <?php else: ?>
                        <span class="text-muted">- Non-Order -</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['branch_name']) ?></span></td>
                    <td class="small"><?= FormatHelper::datetime($p['payment_date']) ?></td>
                    <td>
                        <?php
                        $methodIcons = [
                            'cash'          => '<i class="bi bi-cash me-1 text-success"></i> Tunai',
                            'bank_transfer' => '<i class="bi bi-bank me-1 text-primary"></i> Bank Transfer',
                            'qris'          => '<i class="bi bi-qr-code-scan me-1 text-info"></i> QRIS',
                            'edc'           => '<i class="bi bi-credit-card me-1 text-secondary"></i> EDC'
                        ];
                        echo $methodIcons[$p['payment_method']] ?? htmlspecialchars($p['payment_method']);
                        ?>
                    </td>
                    <td class="text-end fw-bold text-success fs-6"><?= FormatHelper::rupiah($p['amount']) ?></td>
                    <td class="text-center">
                        <?php if ($p['status'] === 'verified'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Verified</span>
                        <?php elseif ($p['status'] === 'pending'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-clock me-1"></i>Pending</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= htmlspecialchars($p['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($p['cashier_name']) ?></td>
                    <td class="text-center">
                        <?php if ($p['status'] === 'pending'): ?>
                        <form method="POST" action="/payments/verify" class="d-inline">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="status" value="verified">
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Verifikasi">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <?php if ($p['order_id']): ?>
                        <a href="/orders/<?= $p['order_id'] ?>/receipt" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak Struk">
                            <i class="bi bi-printer"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
