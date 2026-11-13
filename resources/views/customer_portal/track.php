<?php
use App\Helpers\FormatHelper;
?>

<div class="container py-4">
    <div class="mb-4">
        <a href="/customer-portal" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
        <h4 class="fw-bold mt-2 mb-1"><i class="bi bi-search text-primary me-2"></i>Status Pelacakan Pesanan</h4>
        <p class="text-muted small">Pantau status pengerjaan cetak Anda dari proses antrean hingga siap diambil / dikirim.</p>
    </div>

    <!-- Search box -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <form method="GET" action="/customer-portal/track" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="q" class="form-control" placeholder="Masukkan nomor pesanan (Contoh: ORD-202611-0001) atau nomor HP..." value="<?= htmlspecialchars($keyword) ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-search me-1"></i> Lacak Sekarang
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($keyword) && !$order): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
        <i class="bi bi-folder-x text-danger display-4 mb-3"></i>
        <h5 class="fw-bold">Pesanan Tidak Ditemukan</h5>
        <p class="text-muted small mb-3">Tidak ditemukan pesanan dengan nomor atau kontak "<strong><?= htmlspecialchars($keyword) ?></strong>". Harap pastikan nomor yang Anda masukkan benar.</p>
        <div>
            <a href="/customer-portal" class="btn btn-outline-primary btn-sm px-4">Kembali ke Beranda</a>
        </div>
    </div>
    <?php elseif ($order): ?>

    <!-- Status Stepper Card -->
    <?php
    $stages = [
        'pending'       => ['label' => 'Pesanan Diterima', 'icon' => 'bi-receipt', 'step' => 1],
        'confirmed'     => ['label' => 'Dikonfirmasi', 'icon' => 'bi-check2-circle', 'step' => 2],
        'in_production' => ['label' => 'Sedang Dicetak', 'icon' => 'bi-printer', 'step' => 3],
        'qc'            => ['label' => 'Pemeriksaan Mutu (QC)', 'icon' => 'bi-patch-check', 'step' => 4],
        'ready'         => ['label' => 'Siap Diambil / Kirim', 'icon' => 'bi-box-seam', 'step' => 5],
        'completed'     => ['label' => 'Pesanan Selesai', 'icon' => 'bi-check-all', 'step' => 6]
    ];
    $currentStep = $stages[$order['status']]['step'] ?? 2;
    ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">Pesanan Terverifikasi</span>
                <h4 class="fw-bold mb-0 text-primary font-monospace"><?= htmlspecialchars($order['order_number']) ?></h4>
                <small class="text-muted">Cabang: <strong><?= htmlspecialchars($order['branch_name']) ?></strong> | Tanggal: <?= FormatHelper::datetime($order['order_date']) ?></small>
            </div>
            <div class="text-end">
                <div class="small text-muted">Status Saat Ini:</div>
                <h5 class="fw-bold mb-0 text-success"><?= $stages[$order['status']]['label'] ?? ucfirst($order['status']) ?></h5>
            </div>
        </div>

        <!-- Progress Steps Bar -->
        <div class="position-relative m-4">
            <div class="progress" style="height: 4px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, (($currentStep - 1) / 5) * 100) ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between position-absolute top-0 start-0 w-100 translate-middle-y">
                <?php foreach ($stages as $key => $stg): 
                    $isPassed = $stg['step'] <= $currentStep;
                    $isCurrent = $stg['step'] === $currentStep;
                ?>
                <div class="text-center" style="width: 80px; margin-left: -40px; margin-right: -40px;">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm <?= $isCurrent ? 'bg-primary text-white border border-4 border-primary-subtle' : ($isPassed ? 'bg-primary text-white' : 'bg-light text-muted border') ?>" style="width: 38px; height: 38px;">
                        <i class="bi <?= $stg['icon'] ?> small"></i>
                    </div>
                    <div class="small fw-semibold mt-1 d-none d-md-block <?= $isCurrent ? 'text-primary' : ($isPassed ? 'text-dark' : 'text-muted') ?>">
                        <?= $stg['label'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="height: 35px;"></div>
    </div>

    <!-- Order Items & Details -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-cart-check me-2 text-primary"></i>Rincian Produk Cetak</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <?php if ($item['notes']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($item['notes']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= $item['width'] > 0 ? "{$item['width']} x {$item['length']} cm" : '-' ?></td>
                                <td class="text-center"><?= number_format($item['quantity']) ?></td>
                                <td class="text-end text-muted small"><?= FormatHelper::rupiah($item['unit_price']) ?></td>
                                <td class="text-end fw-semibold"><?= FormatHelper::rupiah($item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($deliveries)): ?>
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-2"><i class="bi bi-truck me-2 text-info"></i>Informasi Pengiriman / Resi</h6>
                    <?php foreach ($deliveries as $del): ?>
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($del['delivery_number']) ?></strong> - <?= htmlspecialchars($del['courier_name']) ?>
                                <?php if ($del['tracking_number']): ?>
                                <span class="badge bg-secondary ms-2">Resi: <?= htmlspecialchars($del['tracking_number']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-primary"><?= ucfirst($del['status']) ?></span>
                        </div>
                        <div class="small text-muted mt-1">Alamat: <?= htmlspecialchars($del['delivery_address']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-cash me-2 text-success"></i>Ringkasan Pembayaran</h6>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Tagihan:</span>
                    <span class="fw-bold"><?= FormatHelper::rupiah($order['total_amount']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sudah Dibayar:</span>
                    <span class="fw-bold text-success"><?= FormatHelper::rupiah($order['paid_amount']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-muted">Sisa Tagihan:</span>
                    <span class="fw-bold text-danger"><?= FormatHelper::rupiah($order['total_amount'] - $order['paid_amount']) ?></span>
                </div>

                <div class="text-center p-3 rounded-3 <?= $order['payment_status'] === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark' ?> mb-3">
                    <div class="small fw-semibold">Status Pembayaran:</div>
                    <div class="fw-bold fs-6">
                        <?= $order['payment_status'] === 'paid' ? '<i class="bi bi-check-circle me-1"></i> LUNAS' : '<i class="bi bi-clock me-1"></i> BELUM LUNAS' ?>
                    </div>
                </div>

                <div class="alert alert-light border small mb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Butuh bantuan atau konfirmasi pembayaran? Hubungi WhatsApp Cabang di <strong><?= htmlspecialchars($order['branch_phone']) ?></strong>.
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
