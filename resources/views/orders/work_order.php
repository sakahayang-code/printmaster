<?php use App\Helpers\FormatHelper; ?>
<div class="container print-container bg-white p-5 border rounded shadow-sm" style="max-width: 850px;">
    <!-- HEADER SPK -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
        <div>
            <h3 class="fw-bold mb-0 text-dark">SURAT PERINTAH KERJA (SPK)</h3>
            <div class="text-muted small">DIVISI PRODUKSI & WORKSHOP PERCETAKAN</div>
            <div class="fw-semibold small mt-1"><?= htmlspecialchars($order['branch_name']) ?></div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold text-primary mb-0"><?= htmlspecialchars($order['order_number']) ?></h4>
            <div class="text-muted small">Tgl Order: <?= FormatHelper::tanggal($order['order_date'], false) ?></div>
            <div class="badge bg-danger fs-6 mt-1">TENGGAT: <?= FormatHelper::tanggal($order['deadline_date']) ?></div>
        </div>
    </div>

    <!-- DATA CUSTOMER -->
    <div class="p-3 bg-light rounded border mb-4">
        <div class="row">
            <div class="col-6">
                <span class="text-muted small">Pelanggan:</span>
                <div class="fw-bold fs-6"><?= htmlspecialchars($order['customer_name']) ?></div>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small">Metode Distribusi:</span>
                <div class="fw-bold"><?= strtoupper(str_replace('_', ' ', $order['pickup_delivery_type'])) ?></div>
            </div>
        </div>
    </div>

    <!-- TABEL ITEM PRODUKSI -->
    <h6 class="fw-bold mb-2">RINCIAN PEKERJAAN & MESIN</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">No. Job</th>
                    <th style="width: 40%;">Spesifikasi Produk & Finishing</th>
                    <th style="width: 15%;">Ukuran & Qty</th>
                    <th style="width: 15%;">Mesin / Operator</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($it['job_number'] ?? ('JOB-' . ($idx + 1))) ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($it['item_name']) ?></div>
                        <?php if (!empty($it['description'])): ?>
                        <div class="small text-muted"><?= htmlspecialchars($it['description']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it['finishing_details'])): ?>
                        <div class="small text-danger fw-semibold mt-1"><i class="bi bi-scissors me-1"></i>Finishing: <?= htmlspecialchars($it['finishing_details']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?= ($it['width_cm'] > 0) ? "{$it['width_cm']} x {$it['height_cm']} cm" : '-' ?></div>
                        <span class="badge bg-dark mt-1"><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></span>
                    </td>
                    <td class="small">
                        <div>Mesin: <?= htmlspecialchars($it['machine_name'] ?? 'Pilih Mesin') ?></div>
                        <div>Operator: <?= htmlspecialchars($it['operator_name'] ?? '-') ?></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- CHECKLIST KONTROL KUALITAS DI WORKSHOP -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="p-3 border rounded h-100">
                <div class="fw-bold small text-uppercase mb-2">Checklist Tahapan Produksi:</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Prepress / File Siap Cetak</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Cetak Mesin Selesai</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Pemotongan / Cutting Pola</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Finishing (Laminating/Mata Ayam/Jilid)</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Quality Control Passed</div>
                <div class="form-check small"><input class="form-check-input" type="checkbox"> Packing & Serah Terima</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 border rounded h-100">
                <div class="fw-bold small text-uppercase mb-2">Catatan Operator & Bahan Baku Terpakai:</div>
                <div class="border-bottom py-2 text-muted small" style="min-height: 25px;">Bahan: ____________________________________</div>
                <div class="border-bottom py-2 text-muted small" style="min-height: 25px;">Jumlah Terpakai: __________________________</div>
                <div class="border-bottom py-2 text-muted small" style="min-height: 25px;">Sisa / Scrap: _____________________________</div>
            </div>
        </div>
    </div>

    <!-- TANDA TANGAN -->
    <div class="row text-center pt-3 border-top">
        <div class="col-4">
            <div class="text-muted small mb-4">Admin Order</div>
            <div class="border-bottom d-inline-block px-4 pb-1">__________________</div>
        </div>
        <div class="col-4">
            <div class="text-muted small mb-4">Operator Produksi</div>
            <div class="border-bottom d-inline-block px-4 pb-1">__________________</div>
        </div>
        <div class="col-4">
            <div class="text-muted small mb-4">Quality Control (QC)</div>
            <div class="border-bottom d-inline-block px-4 pb-1">__________________</div>
        </div>
    </div>
</div>
