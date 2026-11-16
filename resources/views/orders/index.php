<?php
use App\Helpers\FormatHelper;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Pesanan & Penjualan</h3>
        <p class="text-muted small mb-0">Kelola order percetakan, pembayaran, dan pemantauan status pesanan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Auth::isSuperAdmin() || Auth::isOwner()): ?>
            <?php if ($isConsolidated): ?>
                <a href="/orders" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bi bi-geo-alt"></i> Filter Cabang Aktif</a>
            <?php else: ?>
                <a href="/orders?view=all" class="btn btn-primary btn-sm shadow-sm"><i class="bi bi-diagram-3-fill"></i> Semua Cabang</a>
            <?php endif; ?>
        <?php endif; ?>
        <a href="/pos" class="btn btn-success btn-sm shadow-sm"><i class="bi bi-calculator me-1"></i> Buka POS</a>
        <a href="/orders/create" class="btn btn-primary btn-sm shadow-sm"><i class="bi bi-plus-circle me-1"></i> Buat Order Baru</a>
    </div>
</div>

<!-- FILTER & PENCARIAN -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form action="/orders" method="GET" class="row g-2 align-items-center">
            <?php if ($isConsolidated): ?>
            <input type="hidden" name="view" value="all">
            <?php endif; ?>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="No Order / Invoice / Pelanggan..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status Order</option>
                    <option value="pending_confirmation" <?= $status === 'pending_confirmation' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="awaiting_payment" <?= $status === 'awaiting_payment' ? 'selected' : '' ?>>Menunggu Bayar</option>
                    <option value="queued" <?= $status === 'queued' ? 'selected' : '' ?>>Antrean</option>
                    <option value="in_production" <?= $status === 'in_production' ? 'selected' : '' ?>>Produksi</option>
                    <option value="ready_for_pickup" <?= $status === 'ready_for_pickup' ? 'selected' : '' ?>>Siap Diambil</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Selesai</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Batal</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Semua Status Bayar</option>
                    <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>Belum Bayar</option>
                    <option value="partial" <?= $paymentStatus === 'partial' ? 'selected' : '' ?>>DP / Sebagian</option>
                    <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Lunas</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($dateTo ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Cari"><i class="bi bi-search"></i></button>
                <a href="/orders" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- DAFTAR TABEL ORDER -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>No. Order & Tanggal</th>
                        <th>Cabang</th>
                        <th>Pelanggan</th>
                        <th>Item</th>
                        <th>Total Tagihan</th>
                        <th>Terbayar / Sisa</th>
                        <th>Status Bayar</th>
                        <th>Status Order</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Tidak ada pesanan yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="/orders/<?= $o['id'] ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($o['order_number']) ?>
                                </a>
                                <div class="text-xs text-muted" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($o['invoice_number']) ?> • <?= FormatHelper::tanggal($o['order_date'], false) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($o['branch_name']) ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($o['customer_name']) ?></div>
                                <div class="text-muted small"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($o['customer_phone']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $o['total_items'] ?> Item</span>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= FormatHelper::rupiah($o['total_amount']) ?>
                            </td>
                            <td>
                                <div class="text-success small fw-semibold">Bayar: <?= FormatHelper::rupiah($o['paid_amount']) ?></div>
                                <?php if ($o['remaining_amount'] > 0): ?>
                                <div class="text-danger small">Sisa: <?= FormatHelper::rupiah($o['remaining_amount']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= FormatHelper::badgeStatusPayment($o['payment_status']) ?></td>
                            <td><?= FormatHelper::badgeStatusOrder($o['status']) ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail Order">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="/orders/<?= $o['id'] ?>/invoice" target="_blank"><i class="bi bi-receipt me-2"></i>Cetak Invoice A4</a></li>
                                        <li><a class="dropdown-item" href="/orders/<?= $o['id'] ?>/receipt" target="_blank"><i class="bi bi-printer me-2"></i>Cetak Struk Kasir</a></li>
                                        <li><a class="dropdown-item" href="/orders/<?= $o['id'] ?>/work-order" target="_blank"><i class="bi bi-tools me-2"></i>Surat Kerja SPK</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
