<?php
use App\Core\CSRF;
use App\Helpers\FormatHelper;
?>
<div class="mb-4">
    <a href="/orders" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Order</a>
    <h3 class="fw-bold mb-1">Buat Order Percetakan Baru</h3>
    <p class="text-muted small mb-0">Input pesanan kustom percetakan dengan rincian spesifikasi, bahan, finishing, dan pembayaran DP.</p>
</div>

<form action="/orders/store" method="POST" id="formOrder">
    <?= CSRF::field() ?>
    <input type="hidden" name="items" id="itemsPayload">

    <div class="row g-4">
        <!-- KOLOM KIRI: INFORMASI UTAMA & ITEM ORDER -->
        <div class="col-lg-8">
            <!-- KARTU 1: INFORMASI PELANGGAN & CABANG -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Pelanggan & Tenggat Waktu</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Pelanggan <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>) - <?= strtoupper($c['customer_tier']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Order</label>
                            <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tenggat Selesai</label>
                            <input type="date" name="deadline_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Serah Terima</label>
                            <select name="pickup_delivery_type" class="form-select">
                                <option value="pickup">Ambil Sendiri di Cabang (Pickup)</option>
                                <option value="internal_courier">Kurir Internal Toko</option>
                                <option value="expedition">Ekspedisi Pihak Ketiga (JNE/J&T/Grab)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Alamat Pengiriman (Jika Diantar)</label>
                            <input type="text" name="shipping_address" class="form-control" placeholder="Alamat lengkap tujuan...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- KARTU 2: BUILDER ITEM PRODUK PERCETAKAN -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-layers me-2 text-primary"></i>Daftar Item Pesanan</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddItemModal()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item Produk
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="orderItemsTable">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Produk & Spesifikasi</th>
                                    <th>Ukuran</th>
                                    <th>Qty</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noItemAlert" class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada item pesanan yang ditambahkan.<br>
                        Klik tombol <strong>"Tambah Item Produk"</strong> di atas.
                    </div>
                </div>
            </div>

            <!-- KARTU 3: CATATAN & INSTRUKSI KERJA -->
            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-text me-2 text-primary"></i>Instruksi & Catatan Khusus</h6>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Instruksi cetak khusus, nomor kontak darurat, atau catatan desainer..."></textarea>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: RINGKASAN BIAYA & PEMBAYARAN DP -->
        <div class="col-lg-4">
            <div class="card card-custom sticky-top" style="top: 85px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 me-2 text-primary"></i>Ringkasan & Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Voucher Promo</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="voucher_code" id="ordVoucher" class="form-control" placeholder="Kode promo...">
                            <button type="button" class="btn btn-outline-secondary" onclick="checkVoucher()">Terapkan</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Subtotal Item:</span>
                        <span id="txtOrderSubtotal" class="fw-bold text-dark">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Diskon:</span>
                        <span id="txtOrderDiscount" class="text-danger fw-bold">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-dark pt-3 border-top mb-4">
                        <span>Total Tagihan:</span>
                        <span id="txtOrderTotal" class="text-primary">Rp 0</span>
                    </div>

                    <hr>

                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cash me-1"></i>Pembayaran Awal / DP</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nominal Pembayaran (Rp)</label>
                        <input type="number" name="initial_payment" id="ordInitialPay" class="form-control form-control-lg text-end fw-bold" value="0" min="0" oninput="updateRemaining()">
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPayFull()">Lunas</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPayDP(50)">DP 50%</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPayDP(30)">DP 30%</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Tunai (Kasir)</option>
                            <option value="bank_transfer">Transfer Bank (BCA/Mandiri)</option>
                            <option value="qris">QRIS Dinamis</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-secondary">Nomor Bukti / Referensi Bank</label>
                        <input type="text" name="bank_reference" class="form-control form-control-sm" placeholder="Contoh: TRF-BCA-12345">
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Sisa Tagihan:</span>
                            <span id="txtOrderRemaining" class="fw-bold text-danger">Rp 0</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" onclick="submitOrder()">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan & Buat Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL TAMBAH ITEM PRODUK -->
<div class="modal fade" id="modalAddItem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Item Percetakan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Pilih Produk</label>
                        <select id="itemProductSelect" class="form-select" onchange="onItemProductChange()">
                            <option value="">-- Pilih Produk Katalog --</option>
                            <?php foreach ($products as $pr): ?>
                            <option value="<?= $pr['id'] ?>" 
                                    data-price="<?= $pr['base_price'] ?>" 
                                    data-mode="<?= $pr['pricing_mode'] ?>" 
                                    data-name="<?= htmlspecialchars($pr['name']) ?>"
                                    data-setup="<?= $pr['setup_cost'] ?>"
                                    data-waste="<?= $pr['waste_percent'] ?>">
                                <?= htmlspecialchars($pr['name']) ?> (<?= FormatHelper::rupiah($pr['base_price']) ?> / <?= $pr['pricing_mode'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6" id="dimGroupWidth" style="display: none;">
                        <label class="form-label fw-semibold">Lebar (cm)</label>
                        <input type="number" id="itemWidth" class="form-control" value="300" min="10" oninput="recalcItemPreview()">
                    </div>
                    <div class="col-md-6" id="dimGroupHeight" style="display: none;">
                        <label class="form-label fw-semibold">Tinggi (cm)</label>
                        <input type="number" id="itemHeight" class="form-control" value="100" min="10" oninput="recalcItemPreview()">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jumlah (Kuantitas)</label>
                        <input type="number" id="itemQty" class="form-control" value="1" min="1" oninput="recalcItemPreview()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Deskripsi Tambahan / Catatan Spesifikasi</label>
                        <input type="text" id="itemDesc" class="form-control" placeholder="Contoh: Mata ayam tiap 1 meter">
                    </div>

                    <!-- Finishing Options -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Finishing Tambahan</label>
                        <div class="row g-2 border rounded p-2 bg-light">
                            <?php foreach ($finishings as $fn): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input item-fin-check" type="checkbox" value="<?= $fn['id'] ?>"
                                           data-price="<?= $fn['selling_price'] ?>" data-type="<?= $fn['calculation_type'] ?>" data-name="<?= htmlspecialchars($fn['name']) ?>"
                                           id="fn_<?= $fn['id'] ?>" onchange="recalcItemPreview()">
                                    <label class="form-check-label small" for="fn_<?= $fn['id'] ?>">
                                        <?= htmlspecialchars($fn['name']) ?> <span class="text-primary">(+<?= FormatHelper::rupiah($fn['selling_price']) ?>)</span>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Preview Harga -->
                    <div class="col-md-12">
                        <div class="p-3 bg-white border rounded d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <div class="text-muted small">Luas: <span id="itemPreviewArea" class="fw-bold">0</span> m²</div>
                                <div class="text-muted small">Harga Satuan: <span id="itemPreviewUnitRate" class="fw-bold">Rp 0</span></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Subtotal Item:</div>
                                <div class="fs-4 fw-bold text-primary" id="itemPreviewSubtotal">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="addItemToOrderTable()">
                    <i class="bi bi-plus-lg me-1"></i> Tambahkan Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let orderItems = [];
let orderVoucherDiscount = 0;

function openAddItemModal() {
    new bootstrap.Modal(document.getElementById('modalAddItem')).show();
}

function onItemProductChange() {
    const sel = document.getElementById('itemProductSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;

    const mode = opt.getAttribute('data-mode');
    const isArea = (mode === 'area_sqm');

    document.getElementById('dimGroupWidth').style.display = isArea ? 'block' : 'none';
    document.getElementById('dimGroupHeight').style.display = isArea ? 'block' : 'none';

    recalcItemPreview();
}

function recalcItemPreview() {
    const sel = document.getElementById('itemProductSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const basePrice = parseFloat(opt.getAttribute('data-price')) || 0;
    const mode = opt.getAttribute('data-mode');
    const qty = Math.max(1, parseFloat(document.getElementById('itemQty').value) || 1);

    let area = 0;
    let baseSubtotal = 0;

    if (mode === 'area_sqm') {
        const w = parseFloat(document.getElementById('itemWidth').value) || 0;
        const h = parseFloat(document.getElementById('itemHeight').value) || 0;
        area = (w * h) / 10000;
        document.getElementById('itemPreviewArea').innerText = area.toFixed(2);
        baseSubtotal = basePrice * Math.max(1.0, area) * qty;
    } else {
        document.getElementById('itemPreviewArea').innerText = '-';
        baseSubtotal = basePrice * qty;
    }

    let finTotal = 0;
    document.querySelectorAll('.item-fin-check:checked').forEach(cb => {
        const p = parseFloat(cb.getAttribute('data-price')) || 0;
        const type = cb.getAttribute('data-type');
        if (type === 'per_sqm') finTotal += p * Math.max(1.0, area) * qty;
        else if (type === 'per_pcs' || type === 'per_side') finTotal += p * qty;
        else finTotal += p;
    });

    const subtotal = baseSubtotal + finTotal;
    const unitPrice = subtotal / qty;

    document.getElementById('itemPreviewUnitRate').innerText = 'Rp ' + Math.round(unitPrice).toLocaleString('id-ID');
    document.getElementById('itemPreviewSubtotal').innerText = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
}

function addItemToOrderTable() {
    const sel = document.getElementById('itemProductSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        alert('Silakan pilih produk terlebih dahulu!');
        return;
    }

    const productId = opt.value;
    const productName = opt.getAttribute('data-name');
    const mode = opt.getAttribute('data-mode');
    const basePrice = parseFloat(opt.getAttribute('data-price')) || 0;
    const qty = Math.max(1, parseFloat(document.getElementById('itemQty').value) || 1);
    const desc = document.getElementById('itemDesc').value;

    const w = (mode === 'area_sqm') ? (parseFloat(document.getElementById('itemWidth').value) || 0) : 0;
    const h = (mode === 'area_sqm') ? (parseFloat(document.getElementById('itemHeight').value) || 0) : 0;
    const area = (w * h) / 10000;

    let finNames = [];
    let finTotal = 0;
    document.querySelectorAll('.item-fin-check:checked').forEach(cb => {
        finNames.push(cb.getAttribute('data-name'));
        const p = parseFloat(cb.getAttribute('data-price')) || 0;
        const type = cb.getAttribute('data-type');
        if (type === 'per_sqm') finTotal += p * Math.max(1.0, area) * qty;
        else if (type === 'per_pcs' || type === 'per_side') finTotal += p * qty;
        else finTotal += p;
    });

    const baseSubtotal = (mode === 'area_sqm') ? (basePrice * Math.max(1.0, area) * qty) : (basePrice * qty);
    const subtotal = baseSubtotal + finTotal;
    const unitPrice = Math.round(subtotal / qty);

    orderItems.push({
        product_id: productId,
        item_name: productName,
        description: desc,
        pricing_mode: mode,
        width_cm: w,
        height_cm: h,
        area_sqm: area,
        quantity: qty,
        unit: (mode === 'area_sqm') ? 'm2' : 'pcs',
        unit_price: unitPrice,
        cost_estimate: subtotal * 0.40,
        subtotal: subtotal,
        finishing_details: finNames.join(', ')
    });

    bootstrap.Modal.getInstance(document.getElementById('modalAddItem')).hide();
    renderOrderItems();
}

function removeOrderItem(idx) {
    orderItems.splice(idx, 1);
    renderOrderItems();
}

function renderOrderItems() {
    const tbody = document.getElementById('orderItemsBody');
    const noItem = document.getElementById('noItemAlert');
    tbody.innerHTML = '';

    if (orderItems.length === 0) {
        noItem.style.display = 'block';
    } else {
        noItem.style.display = 'none';
        orderItems.forEach((it, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-bold text-dark">${it.item_name}</div>
                    ${it.description ? `<div class="text-muted small">${it.description}</div>` : ''}
                    ${it.finishing_details ? `<span class="badge bg-light text-secondary border small mt-1">${it.finishing_details}</span>` : ''}
                </td>
                <td>${it.width_cm > 0 ? `${it.width_cm}x${it.height_cm} cm` : '-'}</td>
                <td><span class="badge bg-secondary">${it.quantity} ${it.unit}</span></td>
                <td>Rp ${it.unit_price.toLocaleString('id-ID')}</td>
                <td class="fw-bold text-dark">Rp ${Math.round(it.subtotal).toLocaleString('id-ID')}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOrderItem(${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    recalcOrderTotals();
}

let totalBill = 0;

function recalcOrderTotals() {
    const subtotal = orderItems.reduce((acc, it) => acc + it.subtotal, 0);
    totalBill = Math.max(0, subtotal - orderVoucherDiscount);

    document.getElementById('txtOrderSubtotal').innerText = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
    document.getElementById('txtOrderDiscount').innerText = '- Rp ' + Math.round(orderVoucherDiscount).toLocaleString('id-ID');
    document.getElementById('txtOrderTotal').innerText = 'Rp ' + Math.round(totalBill).toLocaleString('id-ID');

    updateRemaining();
}

function checkVoucher() {
    const code = document.getElementById('ordVoucher').value.trim().toUpperCase();
    if (code === 'HEMAT10') {
        const subtotal = orderItems.reduce((acc, it) => acc + it.subtotal, 0);
        orderVoucherDiscount = subtotal * 0.10;
        alert('Voucher HEMAT10 berhasil diterapkan!');
    } else if (code === 'CETAKBANNER') {
        orderVoucherDiscount = 20000;
        alert('Voucher CETAKBANNER berhasil diterapkan!');
    } else {
        orderVoucherDiscount = 0;
        alert('Kode voucher tidak ditemukan.');
    }
    recalcOrderTotals();
}

function setPayFull() {
    document.getElementById('ordInitialPay').value = totalBill;
    updateRemaining();
}

function setPayDP(pct) {
    document.getElementById('ordInitialPay').value = Math.round(totalBill * (pct / 100));
    updateRemaining();
}

function updateRemaining() {
    const pay = parseFloat(document.getElementById('ordInitialPay').value) || 0;
    const remaining = Math.max(0, totalBill - pay);
    document.getElementById('txtOrderRemaining').innerText = 'Rp ' + Math.round(remaining).toLocaleString('id-ID');
}

function submitOrder() {
    if (orderItems.length === 0) {
        alert('Tambahkan minimal 1 item produk terlebih dahulu!');
        return;
    }
    document.getElementById('itemsPayload').value = JSON.stringify(orderItems);
    document.getElementById('formOrder').submit();
}
</script>
