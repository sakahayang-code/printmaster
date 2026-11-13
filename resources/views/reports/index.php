<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Laporan Manajemen & Analitik</h4>
        <p class="text-muted small mb-0">Ringkasan omset penjualan, perbandingan performa antar cabang, analitik kategori produk terlaris, dan ekspor data CSV.</p>
    </div>
    <div class="col-auto d-flex gap-2">
        <a href="/reports/export-csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Periode -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/reports" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Periode Awal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Periode Akhir</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filter Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <small class="text-muted">Total Omset Pesanan</small>
            <h4 class="fw-bold text-primary mb-0 mt-1"><?= FormatHelper::rupiah($summary['total_omset']) ?></h4>
            <small class="text-muted"><?= number_format($summary['total_orders']) ?> transaksi</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <small class="text-muted">Kas/Pembayaran Masuk</small>
            <h4 class="fw-bold text-success mb-0 mt-1"><?= FormatHelper::rupiah($summary['total_paid']) ?></h4>
            <small class="text-success"><i class="bi bi-check-circle me-1"></i>Terverifikasi</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <small class="text-muted">Sisa Piutang Usaha</small>
            <h4 class="fw-bold text-danger mb-0 mt-1"><?= FormatHelper::rupiah($summary['total_piutang']) ?></h4>
            <small class="text-muted">Perlu penagihan</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <small class="text-muted">Rata-rata Nilai Order (AOV)</small>
            <h4 class="fw-bold text-dark mb-0 mt-1">
                <?= FormatHelper::rupiah($summary['total_orders'] > 0 ? ($summary['total_omset'] / $summary['total_orders']) : 0) ?>
            </h4>
            <small class="text-muted">Per transaksi</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Perbandingan Cabang -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-buildings me-2 text-primary"></i>Perbandingan Performa Antar Cabang</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cabang</th>
                            <th class="text-center">Pesanan</th>
                            <th class="text-end">Omset Cabang</th>
                            <th class="text-end">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalAllOmset = array_sum(array_column($branchSales, 'omset'));
                        foreach ($branchSales as $bs): 
                            $pct = $totalAllOmset > 0 ? round(($bs['omset'] / $totalAllOmset) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($bs['branch_name']) ?></strong>
                                <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($bs['branch_code']) ?></span>
                            </td>
                            <td class="text-center"><?= number_format($bs['order_count']) ?></td>
                            <td class="text-end fw-bold text-primary"><?= FormatHelper::rupiah($bs['omset']) ?></td>
                            <td class="text-end">
                                <span class="fw-semibold small"><?= $pct ?>%</span>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kategori Produk Terlaris -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Penjualan Berdasarkan Kategori Produk</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori Produk</th>
                            <th class="text-center">Item Dicetak</th>
                            <th class="text-end">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorySales)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                        <?php else: foreach ($categorySales as $cs): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cs['category_name']) ?></strong></td>
                            <td class="text-center"><?= number_format($cs['item_count']) ?></td>
                            <td class="text-end fw-bold text-success"><?= FormatHelper::rupiah($cs['total_sales']) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Pelanggan -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0">
        <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top 5 Pelanggan Terbesar Periode Ini</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Nomor WhatsApp</th>
                    <th>Tier / Member</th>
                    <th class="text-center">Jumlah Order</th>
                    <th class="text-end">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topCustomers)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi pelanggan.</td></tr>
                <?php else: foreach ($topCustomers as $tc): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($tc['name']) ?></td>
                    <td><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($tc['phone']) ?></td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle"><?= htmlspecialchars($tc['tier_name']) ?></span></td>
                    <td class="text-center fw-semibold"><?= number_format($tc['order_count']) ?></td>
                    <td class="text-end fw-bold text-primary fs-6"><?= FormatHelper::rupiah($tc['total_spent']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
