<?php
use App\Helpers\FormatHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px; color: #222; background: #fff; }
        .surat-jalan-box { max-width: 800px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; }
        @media print {
            .no-print { display: none !important; }
            .surat-jalan-box { border: none; padding: 0; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Cetak Surat Jalan</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup Halaman</button>
    </div>

    <div class="surat-jalan-box">
        <div class="row align-items-center border-bottom pb-3 mb-3">
            <div class="col-8">
                <h4 class="fw-bold mb-0 text-primary">PRINTMASTER</h4>
                <div class="fw-semibold"><?= htmlspecialchars($do['branch_name']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($do['branch_address']) ?> | Telp: <?= htmlspecialchars($do['branch_phone']) ?></small>
            </div>
            <div class="col-4 text-end">
                <h5 class="fw-bold text-uppercase mb-1">SURAT JALAN</h5>
                <div class="font-monospace fw-bold fs-6 text-dark"><?= htmlspecialchars($do['delivery_number']) ?></div>
                <small class="text-muted">Tanggal: <?= FormatHelper::date($do['created_at']) ?></small>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <small class="text-muted text-uppercase fw-bold">Tujuan Pengiriman / Penerima:</small>
                <div class="fw-bold fs-6"><?= htmlspecialchars($do['recipient_name']) ?></div>
                <?php if ($do['customer_company']): ?><div><?= htmlspecialchars($do['customer_company']) ?></div><?php endif; ?>
                <div>Telp: <strong><?= htmlspecialchars($do['recipient_phone']) ?></strong></div>
                <div class="text-muted small mt-1"><?= nl2br(htmlspecialchars($do['delivery_address'])) ?></div>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted text-uppercase fw-bold">Informasi Ekspedisi & Pesanan:</small>
                <div>No. Pesanan: <strong><?= htmlspecialchars($do['order_number']) ?></strong></div>
                <div>Kurir / Ekspedisi: <strong><?= htmlspecialchars($do['courier_name']) ?></strong></div>
                <?php if ($do['tracking_number']): ?>
                <div>No. Resi: <strong class="font-monospace"><?= htmlspecialchars($do['tracking_number']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;" class="text-center">No</th>
                    <th>Nama Produk / Layanan Cetak</th>
                    <th>Spesifikasi Ukuran</th>
                    <th style="width: 100px;" class="text-center">Qty Dikirim</th>
                    <th style="width: 120px;" class="text-center">Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="fw-semibold">
                        <?= htmlspecialchars($item['product_name']) ?>
                        <?php if ($item['notes']): ?>
                        <div class="text-muted small"><?= htmlspecialchars($item['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= $item['width'] > 0 ? "{$item['width']} x {$item['length']} cm" : '-' ?></td>
                    <td class="text-center fw-bold"><?= number_format($item['quantity']) ?></td>
                    <td class="text-center text-muted"><?= htmlspecialchars($item['unit'] ?? 'pcs') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($do['notes']): ?>
        <div class="alert alert-light border small mb-4">
            <strong>Catatan Pengantaran:</strong> <?= htmlspecialchars($do['notes']) ?>
        </div>
        <?php endif; ?>

        <div class="row text-center mt-5">
            <div class="col-4">
                <small class="text-muted">Pengirim / Bagian Logistik</small>
                <div style="height: 70px;"></div>
                <div class="fw-bold border-top pt-1">( <?= htmlspecialchars($do['branch_name']) ?> )</div>
            </div>
            <div class="col-4">
                <small class="text-muted">Kurir / Petugas Ekspedisi</small>
                <div style="height: 70px;"></div>
                <div class="fw-bold border-top pt-1">( <?= htmlspecialchars($do['courier_name']) ?> )</div>
            </div>
            <div class="col-4">
                <small class="text-muted">Penerima Barang</small>
                <div style="height: 70px;"></div>
                <div class="fw-bold border-top pt-1">( <?= htmlspecialchars($do['recipient_name']) ?> )</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-3 border-top">
            Harap periksa kelengkapan dan kondisi barang saat diterima. Surat Jalan ini merupakan bukti serah terima resmi.
        </div>
    </div>
</body>
</html>
