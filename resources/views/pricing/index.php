<?php
use App\Helpers\FormatHelper;
use App\Core\Auth;
use App\Core\CSRF;
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Kalkulator Harga & Estimasi Biaya Produksi</h3>
    <p class="text-muted small mb-0">Hitung estimasi kebutuhan bahan baku, biaya mesin, finishing, waste, dan margin profit secara akurat.</p>
</div>

<div class="row g-4">
    <!-- PANEL INPUT KALKULATOR -->
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Parameter Produk & Ukuran</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Produk Percetakan</label>
                    <select id="calcProduct" class="form-select form-select-lg" onchange="runCalc()">
                        <?php foreach ($products as $pr): ?>
                        <option value="<?= $pr['id'] ?>" data-mode="<?= $pr['pricing_mode'] ?>">
                            <?= htmlspecialchars($pr['name']) ?> (<?= strtoupper($pr['pricing_mode']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3" id="calcDimRow">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Lebar (cm)</label>
                        <input type="number" id="calcWidth" class="form-control" value="300" min="1" oninput="runCalc()">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tinggi (cm)</label>
                        <input type="number" id="calcHeight" class="form-control" value="100" min="1" oninput="runCalc()">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Kuantitas</label>
                    <input type="number" id="calcQty" class="form-control" value="1" min="1" oninput="runCalc()">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Opsi Finishing</label>
                    <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($finishings as $fn): ?>
                        <div class="form-check py-1">
                            <input class="form-check-input calc-fin" type="checkbox" value="<?= $fn['id'] ?>" id="cfin_<?= $fn['id'] ?>" onchange="runCalc()">
                            <label class="form-check-label d-flex justify-content-between small w-100" for="cfin_<?= $fn['id'] ?>">
                                <span><?= htmlspecialchars($fn['name']) ?></span>
                                <span class="text-primary fw-semibold">+<?= FormatHelper::rupiah($fn['selling_price']) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL BREAKDOWN HASIL PERHITUNGAN -->
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Rincian Komponen Biaya & Harga Jual</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Kalkulasi Otomatis</span>
            </div>
            <div class="card-body">
                <!-- HARGA JUAL UTAMA -->
                <div class="p-3 bg-light rounded border text-center mb-4">
                    <div class="text-muted small text-uppercase fw-semibold">Rekomendasi Harga Jual Konsumen</div>
                    <div class="display-6 fw-bold text-primary mt-1" id="resSellingPrice">Rp 0</div>
                    <div class="text-muted small mt-1">Harga per unit: <strong id="resUnitPrice" class="text-dark">Rp 0</strong></div>
                </div>

                <!-- RINCIAN TEKNIS INTERNAL -->
                <h6 class="fw-bold small text-uppercase text-secondary mb-3">Breakdown Komponen HPP (Internal Staff)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-3">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 55%;">Luas / Ukuran Efektif</td>
                                <td class="text-end fw-semibold" id="resArea">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estimasi Biaya Bahan Baku Pokok</td>
                                <td class="text-end fw-semibold" id="resRawCost">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Biaya Finishing Tambahan</td>
                                <td class="text-end fw-semibold" id="resFinishingCost">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Toleransi Waste & Makeready (<span id="resWastePct">0</span>%)</td>
                                <td class="text-end fw-semibold" id="resWasteCost">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Biaya Setup & Overhead Mesin</td>
                                <td class="text-end fw-semibold" id="resOverhead">Rp 0</td>
                            </tr>
                            <tr class="table-warning fw-bold">
                                <td>TOTAL ESTIMASI HPP PRODUKSI</td>
                                <td class="text-end" id="resTotalHPP">Rp 0</td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td>ESTIMASI LABA KOTOR (GROSS MARGIN)</td>
                                <td class="text-end" id="resMargin">Rp 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2">
                    <a href="/orders/create" class="btn btn-primary fw-semibold">
                        <i class="bi bi-cart-plus me-1"></i> Jadikan Order Sekarang
                    </a>
                    <a href="/quotes/create" class="btn btn-outline-secondary fw-semibold">
                        <i class="bi bi-file-earmark-text me-1"></i> Buat Surat Penawaran Resmi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runCalc() {
    const sel = document.getElementById('calcProduct');
    const opt = sel.options[sel.selectedIndex];
    const mode = opt.getAttribute('data-mode');
    
    document.getElementById('calcDimRow').style.display = (mode === 'area_sqm') ? 'flex' : 'none';

    const prodId = opt.value;
    const w = parseFloat(document.getElementById('calcWidth').value) || 0;
    const h = parseFloat(document.getElementById('calcHeight').value) || 0;
    const qty = parseFloat(document.getElementById('calcQty').value) || 1;

    let finIds = [];
    document.querySelectorAll('.calc-fin:checked').forEach(cb => finIds.push(cb.value));

    const calcUrl = (window.APP_BASE_URL || '') + '/pricing/calculate-ajax';
    fetch(calcUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: prodId,
            width_cm: w,
            height_cm: h,
            quantity: qty,
            finishing_ids: finIds
        })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('resSellingPrice').innerText = 'Rp ' + Math.round(data.selling_subtotal).toLocaleString('id-ID');
        document.getElementById('resUnitPrice').innerText = 'Rp ' + Math.round(data.unit_price).toLocaleString('id-ID');
        document.getElementById('resArea').innerText = (data.area_sqm > 0) ? (data.area_sqm + ' m² (' + data.width_cm + 'x' + data.height_cm + ' cm)') : '-';
        document.getElementById('resRawCost').innerText = 'Rp ' + Math.round(data.subtotal_base * 0.40).toLocaleString('id-ID');
        document.getElementById('resFinishingCost').innerText = 'Rp ' + Math.round(data.finishing_total).toLocaleString('id-ID');
        document.getElementById('resWastePct').innerText = data.waste_pct;
        document.getElementById('resWasteCost').innerText = 'Rp ' + Math.round(data.waste_cost).toLocaleString('id-ID');
        document.getElementById('resOverhead').innerText = 'Rp ' + Math.round(data.setup_cost + data.overhead_cost).toLocaleString('id-ID');
        document.getElementById('resTotalHPP').innerText = 'Rp ' + Math.round(data.cost_estimate_total).toLocaleString('id-ID');
        document.getElementById('resMargin').innerText = 'Rp ' + Math.round(data.estimated_gross_margin).toLocaleString('id-ID');
    });
}

document.addEventListener('DOMContentLoaded', runCalc);
</script>
