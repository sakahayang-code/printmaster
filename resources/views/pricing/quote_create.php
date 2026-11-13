<?php
use App\Core\CSRF;
use App\Helpers\FormatHelper;
?>
<div class="mb-4">
    <a href="/quotes" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penawaran</a>
    <h3 class="fw-bold mb-1">Buat Surat Penawaran Harga Baru</h3>
    <p class="text-muted small mb-0">Rancang penawaran harga dengan spesifikasi produk sebelum deal menjadi pesanan.</p>
</div>

<form action="/quotes/store" method="POST" id="formQuote">
    <?= CSRF::field() ?>
    <input type="hidden" name="items" id="quoteItemsPayload">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person me-2 text-primary"></i>Pilih Calon Pelanggan & Validitas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pelanggan</label>
                            <select name="customer_id" class="form-select" required>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Berlaku Sampai Tanggal</label>
                            <input type="date" name="valid_until" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Item yang Ditawarkan</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddQuoteItem()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Item Produk</th>
                                    <th>Ukuran</th>
                                    <th>Qty</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="quoteItemsBody">
                                <!-- JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noQuoteItems" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                        Belum ada item penawaran. Klik tombol di atas untuk menambahkan.
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-left-dots me-2 text-primary"></i>Catatan Penawaran</h6>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Syarat & ketentuan penawaran harga..."></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom sticky-top" style="top: 85px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">Ringkasan Penawaran</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between fs-5 fw-bold text-dark mb-4">
                        <span>Total Nilai:</span>
                        <span id="txtQuoteTotal" class="text-primary">Rp 0</span>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" onclick="submitQuote()">
                        <i class="bi bi-save me-1"></i> Simpan Surat Penawaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL TAMBAH ITEM QUOTE -->
<div class="modal fade" id="modalQuoteItem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Item Penawaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Produk</label>
                    <select id="qProdSelect" class="form-select" onchange="calcQuoteModal()">
                        <?php foreach ($products as $pr): ?>
                        <option value="<?= $pr['id'] ?>" data-price="<?= $pr['base_price'] ?>" data-mode="<?= $pr['pricing_mode'] ?>" data-name="<?= htmlspecialchars($pr['name']) ?>">
                            <?= htmlspecialchars($pr['name']) ?> (<?= FormatHelper::rupiah($pr['base_price']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3" id="qDimRow">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Lebar (cm)</label>
                        <input type="number" id="qWidth" class="form-control" value="200" oninput="calcQuoteModal()">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tinggi (cm)</label>
                        <input type="number" id="qHeight" class="form-control" value="100" oninput="calcQuoteModal()">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Qty</label>
                    <input type="number" id="qQty" class="form-control" value="1" min="1" oninput="calcQuoteModal()">
                </div>
                <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Subtotal:</span>
                    <span id="qSubtotalTxt" class="fw-bold fs-5 text-primary">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="addQuoteItem()">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<script>
let qItems = [];

function openAddQuoteItem() {
    calcQuoteModal();
    new bootstrap.Modal(document.getElementById('modalQuoteItem')).show();
}

function calcQuoteModal() {
    const sel = document.getElementById('qProdSelect');
    const opt = sel.options[sel.selectedIndex];
    const mode = opt.getAttribute('data-mode');
    const price = parseFloat(opt.getAttribute('data-price')) || 0;
    const qty = Math.max(1, parseFloat(document.getElementById('qQty').value) || 1);

    document.getElementById('qDimRow').style.display = (mode === 'area_sqm') ? 'flex' : 'none';

    let sub = 0;
    if (mode === 'area_sqm') {
        const w = parseFloat(document.getElementById('qWidth').value) || 0;
        const h = parseFloat(document.getElementById('qHeight').value) || 0;
        const area = (w * h) / 10000;
        sub = price * Math.max(1.0, area) * qty;
    } else {
        sub = price * qty;
    }
    document.getElementById('qSubtotalTxt').innerText = 'Rp ' + Math.round(sub).toLocaleString('id-ID');
}

function addQuoteItem() {
    const sel = document.getElementById('qProdSelect');
    const opt = sel.options[sel.selectedIndex];
    const mode = opt.getAttribute('data-mode');
    const price = parseFloat(opt.getAttribute('data-price')) || 0;
    const qty = Math.max(1, parseFloat(document.getElementById('qQty').value) || 1);
    const w = (mode === 'area_sqm') ? (parseFloat(document.getElementById('qWidth').value) || 0) : 0;
    const h = (mode === 'area_sqm') ? (parseFloat(document.getElementById('qHeight').value) || 0) : 0;
    const area = (w * h) / 10000;

    let sub = (mode === 'area_sqm') ? (price * Math.max(1.0, area) * qty) : (price * qty);

    qItems.push({
        product_id: opt.value,
        item_name: opt.getAttribute('data-name'),
        pricing_mode: mode,
        width_cm: w,
        height_cm: h,
        area_sqm: area,
        quantity: qty,
        unit: (mode === 'area_sqm') ? 'm2' : 'pcs',
        unit_price: Math.round(sub / qty),
        cost_estimate: sub * 0.40,
        subtotal: sub,
        finishing_details: ''
    });

    bootstrap.Modal.getInstance(document.getElementById('modalQuoteItem')).hide();
    renderQuoteItems();
}

function removeQuoteItem(idx) {
    qItems.splice(idx, 1);
    renderQuoteItems();
}

function renderQuoteItems() {
    const tbody = document.getElementById('quoteItemsBody');
    const no = document.getElementById('noQuoteItems');
    tbody.innerHTML = '';

    if (qItems.length === 0) {
        no.style.display = 'block';
    } else {
        no.style.display = 'none';
        qItems.forEach((it, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-bold">${it.item_name}</td>
                <td>${it.width_cm > 0 ? `${it.width_cm}x${it.height_cm} cm` : '-'}</td>
                <td><span class="badge bg-secondary">${it.quantity} ${it.unit}</span></td>
                <td>Rp ${it.unit_price.toLocaleString('id-ID')}</td>
                <td class="fw-bold">Rp ${Math.round(it.subtotal).toLocaleString('id-ID')}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuoteItem(${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    const total = qItems.reduce((acc, it) => acc + it.subtotal, 0);
    document.getElementById('txtQuoteTotal').innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

function submitQuote() {
    if (qItems.length === 0) {
        alert('Tambahkan minimal 1 item!');
        return;
    }
    document.getElementById('quoteItemsPayload').value = JSON.stringify(qItems);
    document.getElementById('formQuote').submit();
}
</script>
