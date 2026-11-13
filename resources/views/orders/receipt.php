<?php use App\Helpers\FormatHelper; ?>
<div class="print-container bg-white p-3 mx-auto border" style="max-width: 320px; font-family: 'Courier New', Courier, monospace; font-size: 12px;">
    <div class="text-center mb-3">
        <div class="fw-bold fs-6"><?= strtoupper(htmlspecialchars($order['company_name'])) ?></div>
        <div class="small"><?= htmlspecialchars($order['branch_name']) ?></div>
        <div class="small"><?= htmlspecialchars($order['branch_phone']) ?></div>
        <div class="border-bottom border-dark my-2"></div>
    </div>

    <div class="small mb-2">
        <div>No: <?= htmlspecialchars($order['order_number']) ?></div>
        <div>Tgl: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
        <div>Cust: <?= htmlspecialchars($order['customer_name']) ?></div>
    </div>

    <div class="border-top border-bottom border-dark py-1 my-2">
        <?php foreach ($items as $it): ?>
        <div class="mb-1">
            <div class="fw-bold"><?= htmlspecialchars($it['item_name']) ?></div>
            <div class="d-flex justify-content-between">
                <span><?= $it['quantity'] ?> <?= $it['unit'] ?> x <?= number_format($it['unit_price'], 0, ',', '.') ?></span>
                <span><?= number_format($it['subtotal'], 0, ',', '.') ?></span>
            </div>
            <?php if (!empty($it['finishing_details'])): ?>
            <div style="font-size: 10px; color: #555;">Fin: <?= htmlspecialchars($it['finishing_details']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="small mb-2">
        <div class="d-flex justify-content-between">
            <span>Subtotal:</span>
            <span>Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
        </div>
        <?php if ($order['discount_amount'] > 0): ?>
        <div class="d-flex justify-content-between">
            <span>Diskon:</span>
            <span>-Rp <?= number_format($order['discount_amount'], 0, ',', '.') ?></span>
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between fw-bold fs-6 my-1">
            <span>TOTAL:</span>
            <span>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Dibayar:</span>
            <span>Rp <?= number_format($order['paid_amount'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Sisa:</span>
            <span>Rp <?= number_format($order['remaining_amount'], 0, ',', '.') ?></span>
        </div>
    </div>

    <div class="border-top border-dark text-center pt-2 small">
        <div>Status: <?= strtoupper($order['payment_status']) ?></div>
        <div class="mt-2">Simpan struk ini sebagai bukti pengambilan barang.</div>
        <div class="mt-1">Terima kasih atas kunjungan Anda!</div>
    </div>
</div>
