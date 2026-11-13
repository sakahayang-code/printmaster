<?php
use App\Helpers\FormatHelper;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Dashboard Operasional</h3>
        <p class="text-muted small mb-0">
            <?= $isConsolidated ? '<span class="badge bg-purple text-dark bg-opacity-25 border border-purple">Mode Konsolidasi Seluruh Cabang</span>' : 'Memantau aktivitas operasional di ' . htmlspecialchars($activeBranch['name'] ?? '') ?>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if (Auth::isSuperAdmin() || Auth::isOwner()): ?>
            <?php if ($isConsolidated): ?>
                <a href="/dashboard" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-geo-alt"></i> Tampilkan Per Cabang
                </a>
            <?php else: ?>
                <a href="/dashboard?view=all" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-diagram-3-fill"></i> Lihat Konsolidasi Seluruh Cabang
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (Auth::hasPermission('orders.create')): ?>
        <a href="/pos" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-cart-plus me-1"></i> Buka Kasir POS
        </a>
        <a href="/orders/create" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Order Percetakan Baru
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- 4 KARTU STATISTIK UTAMA -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Omzet Bulan Ini</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= FormatHelper::rupiah($omzetBulanIni) ?></div>
                    <div class="text-success small mt-1"><i class="bi bi-calendar-check"></i> Periode <?= date('M Y') ?></div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Pesanan</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= number_format($totalOrderBulanIni, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Order</span></div>
                    <div class="text-muted small mt-1"><i class="bi bi-receipt"></i> Transaksi terekam</div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-bag-check-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Sedang Diproduksi</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= number_format($inProductionCount, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Pekerjaan</span></div>
                    <div class="text-warning-emphasis small mt-1"><i class="bi bi-gear-fill"></i> Jalur cetak & finishing</div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-cpu-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Piutang Pelanggan</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= FormatHelper::rupiah($totalPiutang) ?></div>
                    <div class="text-danger small mt-1"><i class="bi bi-clock"></i> Belum lunas</div>
                </div>
                <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                    <i class="bi bi-hourglass-bottom fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK & KINERJA CABANG -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Tren Penjualan 7 Hari Terakhir</h6>
                <span class="badge bg-light text-secondary border">Harian</span>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Kinerja Cabang Bulan Ini</h6>
                <span class="badge bg-light text-secondary border">Target vs Realisasi</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($branchSummaries)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Cabang</th>
                                <th>Order</th>
                                <th>Omzet</th>
                                <th>Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($branchSummaries as $bs): 
                                $pct = ($bs['monthly_target'] > 0) ? min(100, round(($bs['total_omzet'] / $bs['monthly_target']) * 100)) : 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($bs['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($bs['code']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $bs['total_orders'] ?></span></td>
                                <td class="fw-semibold"><?= FormatHelper::rupiah($bs['total_omzet']) ?></td>
                                <td style="width: 120px;">
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;"><?= $pct ?>%</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                    Ringkasan performa cabang saat ini: <strong><?= htmlspecialchars($activeBranch['name'] ?? '') ?></strong>.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- TABEL PESANAN TERBARU & BAHAN MINIMUM -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Pesanan Masuk Terbaru</h6>
                <a href="/orders" class="btn btn-outline-primary btn-sm">Lihat Semua Order</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Order</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status Order</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada pesanan yang tercatat.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td>
                                        <a href="/orders/<?= $ro['id'] ?>" class="fw-bold text-decoration-none">
                                            <?= htmlspecialchars($ro['order_number']) ?>
                                        </a>
                                        <div class="text-xs text-muted" style="font-size: 0.75rem;"><?= FormatHelper::tanggal($ro['order_date'], false) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($ro['customer_name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($ro['branch_name']) ?></div>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?= FormatHelper::rupiah($ro['total_amount']) ?>
                                    </td>
                                    <td><?= FormatHelper::badgeStatusPayment($ro['payment_status']) ?></td>
                                    <td><?= FormatHelper::badgeStatusOrder($ro['status']) ?></td>
                                    <td class="text-end">
                                        <a href="/orders/<?= $ro['id'] ?>" class="btn btn-sm btn-light border" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
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

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-octagon me-2"></i>Peringatan Stok Menipis</h6>
                <a href="/inventory" class="btn btn-outline-secondary btn-sm">Gudang</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockMaterials)): ?>
                <div class="p-3 text-center text-success small">
                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>
                    Semua stok bahan baku berada pada tingkat aman.
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($lowStockMaterials as $mat): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div>
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($mat['name']) ?></div>
                            <small class="text-muted">Min: <?= number_format($mat['min_stock'], 1) ?> <?= htmlspecialchars($mat['unit']) ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger"><?= number_format($mat['current_stock'], 1) ?> <?= htmlspecialchars($mat['unit']) ?></span>
                            <div class="mt-1">
                                <a href="/purchasing?material_id=<?= $mat['id'] ?>" class="badge bg-primary text-decoration-none">Order PO</a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban me-2 text-primary"></i>Antrean Mesin & Produksi</h6>
                <a href="/production" class="btn btn-outline-primary btn-sm">Papan Produksi</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentJobs)): ?>
                <div class="p-3 text-center text-muted small">Tidak ada antrean mesin saat ini.</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentJobs as $job): ?>
                    <li class="list-group-item py-2 px-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($job['job_number']) ?></span>
                                <div class="text-muted small"><?= htmlspecialchars($job['item_name']) ?></div>
                            </div>
                            <div><?= FormatHelper::badgeStatusJob($job['status']) ?></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <span><i class="bi bi-cpu me-1"></i><?= htmlspecialchars($job['machine_name'] ?? 'Belum dipilih') ?></span>
                            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($job['operator_name'] ?? '-') ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('salesTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartDates) ?>,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: <?= json_encode($chartSales) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
