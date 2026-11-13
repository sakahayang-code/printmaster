<?php use App\Helpers\FormatHelper; ?>
<div class="container print-container bg-white p-5 border rounded shadow-sm" style="max-width: 850px;">
    <!-- HEADER INVOICE -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-sm-7">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-printer-fill fs-2 text-primary"></i>
                <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($order['company_name']) ?></h3>
            </div>
            <div class="text-muted small"><?= htmlspecialchars($order['branch_name']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($order['branch_address']) ?></div>
            <div class="text-muted small">Telp: <?= htmlspecialchars($order['branch_phone']) ?> • NPWP: <?= htmlspecialchars($order['tax_number']) ?></div>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <h2 class="fw-bold text-uppercase text-primary mb-1">INVOICE</h2>
            <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($order['invoice_number']) ?></div>
            <div class="text-muted small">No. Order: <?= htmlspecialchars($order['order_number']) ?></div>
            <div class="text-muted small">Tanggal: <?= FormatHelper::tanggal($order['order_date'], false) ?></div>
        </div>
    </div>

    <!-- PELANGGAN & PENERIMA -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Tagihan Kepada:</div>
            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($order['customer_name']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($order['customer_phone']) ?></div>
            <?php if (!empty($order['customer_address'])): ?>
            <div class="text-muted small"><?= htmlspecialchars($order['customer_address']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Jatuh Tempo / Tenggat:</div>
            <div class="fw-bold text-dark"><?= FormatHelper::tanggal($order['deadline_date']) ?></div>
            <div class="text-muted small mt-1">Status: <?= strtoupper($order['payment_status']) ?></div>
            <div class="text-muted small">Metode: <?= strtoupper(str_replace('_', ' ', $order['pickup_delivery_type'])) ?></div>
        </div>
    </div>

    <!-- TABEL ITEM -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Deskripsi Produk & Spesifikasi</th>
                    <th style="width: 15%;">Ukuran</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 12%;" class="text-end">Harga</th>
                    <th style="width: 13%;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($it['item_name']) ?></div>
                        <?php if (!empty($it['description'])): ?>
                        <div class="text-muted small"><?= htmlspecialchars($it['description']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it['finishing_details'])): ?>
                        <small class="text-secondary d-block mt-1">Finishing: <?= htmlspecialchars($it['finishing_details']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= ($it['width_cm'] > 0) ? "{$it['width_cm']}x{$it['height_cm']}cm" : '-' ?></td>
                    <td class="text-center"><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></td>
                    <td class="text-end"><?= FormatHelper::rupiah($it['unit_price']) ?></td>
                    <td class="text-end fw-semibold"><?= FormatHelper::rupiah($it['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Subtotal:</th>
                    <th class="text-end"><?= FormatHelper::rupiah($order['subtotal']) ?></th>
                </tr>
                <?php if ($order['discount_amount'] > 0): ?>
                <tr>
                    <th colspan="5" class="text-end text-danger">Diskon <?= $order['voucher_code'] ? "({$order['voucher_code']})" : '' ?>:</th>
                    <th class="text-end text-danger">- <?= FormatHelper::rupiah($order['discount_amount']) ?></th>
                </tr>
                <?php endif; ?>
                <tr>
                    <th colspan="5" class="text-end fs-6">TOTAL:</th>
                    <th class="text-end fs-6 text-primary fw-bold"><?= FormatHelper::rupiah($order['total_amount']) ?></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end text-success">Sudah Dibayar:</th>
                    <th class="text-end text-success fw-bold"><?= FormatHelper::rupiah($order['paid_amount']) ?></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end text-danger">Sisa Tagihan:</th>
                    <th class="text-end text-danger fw-bold"><?= FormatHelper::rupiah($order['remaining_amount']) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- INFORMASI REKENING & TANDA TANGAN -->
    <div class="row pt-3 border-top">
        <div class="col-7">
            <div class="p-3 bg-light rounded border small">
                <div class="fw-bold mb-1"><i class="bi bi-bank me-1"></i> Rekening Pembayaran Resmi:</div>
                <div>Bank BCA: <strong>8899-0011-22</strong> a/n PT Master Grafika Indonesia</div>
                <div>Bank Mandiri: <strong>123-00-998877-6</strong> a/n PT Master Grafika Indonesia</div>
                <div class="mt-2 text-muted" style="font-size: 0.75rem;">Harap cantumkan nomor invoice <strong><?= htmlspecialchars($order['invoice_number']) ?></strong> pada berita transfer.</div>
            </div>
        </div>
        <div class="col-5 text-center">
            <div class="text-muted small mb-5">Hormat Kami,<br><strong><?= htmlspecialchars($order['branch_name']) ?></strong></div>
            <div class="fw-bold text-dark border-bottom d-inline-block px-4 pb-1">Bagian Keuangan / Kasir</div>
        </div>
    </div>
</div>
