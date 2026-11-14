<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="row g-3">
    <!-- JIKA SHIFT KASIR BELUM BUKA -->
    <?php if (!$shift): ?>
    <div class="col-12">
        <div class="alert alert-warning border-warning d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <strong>Shift Kasir Belum Dibuka!</strong> Anda harus membuka shift dan mencatat modal awal kasir sebelum melayani transaksi POS.
            </div>
            <button class="btn btn-warning fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalOpenShift">
                <i class="bi bi-key-fill me-1"></i> Buka Shift Sekarang
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- PANEL KIRI: KATALOG PRODUK -->
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-sm-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="productSearch" class="form-control border-start-0" placeholder="Cari nama produk / kode...">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <select id="categoryFilter" class="form-select">
                            <option value="all">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 overflow-auto" style="max-height: 650px;">
                <div class="row g-2" id="productGrid">
                    <?php foreach ($products as $p): ?>
                    <div class="col-sm-6 col-md-4 product-item" data-category="<?= $p['category_id'] ?>" data-name="<?= strtolower($p['name'] . ' ' . $p['code']) ?>">
                        <div class="card h-100 border p-2 product-card shadow-sm text-start" 
                             style="cursor: pointer; transition: all 0.15s ease;"
                             onclick="handleProductClick(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-light text-secondary border small text-xs"><?= htmlspecialchars($p['code']) ?></span>
                                <span class="badge bg-primary-subtle text-primary border small" style="font-size: 0.7rem;"><?= htmlspecialchars($p['pricing_mode']) ?></span>
                            </div>
                            <div class="fw-bold text-dark text-truncate mb-1" title="<?= htmlspecialchars($p['name']) ?>">
                                <?= htmlspecialchars($p['name']) ?>
                            </div>
                            <div class="text-xs text-muted mb-2 text-truncate" style="font-size: 0.75rem;">
                                <?= htmlspecialchars($p['category_name']) ?>
                            </div>
                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary"><?= FormatHelper::rupiah($p['base_price']) ?></span>
                                <span class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill"><i class="bi bi-plus"></i></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL KANAN: KERANJANG BELANJA & CHECKOUT -->
    <div class="col-lg-5">
        <div class="card card-custom h-100 d-flex flex-column">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cart3 me-2 text-primary"></i>Keranjang Kasir</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                        <i class="bi bi-trash"></i> Kosongkan
                    </button>
                </div>
            </div>

            <!-- Pilih Pelanggan -->
            <div class="p-3 bg-light border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-secondary mb-0">PELANGGAN</label>
                    <span id="tierBadge" class="badge bg-secondary">Reguler</span>
                </div>
                <select id="customerSelect" class="form-select form-select-sm" onchange="updateCustomerDiscount()">
                    <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" data-discount="<?= $c['discount_percent'] ?>" data-tier="<?= $c['customer_tier'] ?>">
                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>) - <?= strtoupper($c['customer_tier']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Daftar Item Keranjang -->
            <div class="card-body p-0 flex-grow-1 overflow-auto" style="max-height: 380px;">
                <table class="table table-sm align-middle mb-0" id="cartTable">
                    <tbody id="cartItems">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
                <div id="cartEmptyState" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                    Keranjang masih kosong.<br>Klik produk di sebelah kiri untuk menambahkan.
                </div>
            </div>

            <!-- Ringkasan Total & Pembayaran -->
            <div class="card-footer bg-white p-3 border-top mt-auto">
                <!-- Voucher Input -->
                <div class="input-group input-group-sm mb-2">
                    <input type="text" id="voucherCode" class="form-control" placeholder="Kode voucher promo (opsional)">
                    <button class="btn btn-outline-secondary" type="button" onclick="applyVoucher()">Gunakan</button>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal:</span>
                    <span id="txtSubtotal" class="fw-semibold text-dark">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Diskon (Member / Promo):</span>
                    <span id="txtDiscount" class="text-danger fw-semibold">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between fs-5 fw-bold text-dark pt-2 border-top mb-3">
                    <span>Total Pembayaran:</span>
                    <span id="txtTotal" class="text-primary">Rp 0</span>
                </div>

                <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" id="btnCheckout" onclick="openPaymentModal()" <?= !$shift ? 'disabled' : '' ?>>
                    <i class="bi bi-wallet2 me-1"></i> Proses Pembayaran & Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BUKA SHIFT KASIR -->
<div class="modal fade" id="modalOpenShift" tabindex="-1">
    <div class="modal-dialog">
        <form action="/cash-shifts/open" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-key-fill me-2"></i>Buka Shift Kasir Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Mulai sesi kerja kasir dengan mencatat saldo awal laci kas (kas kecil modal kembalian).</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kas Register</label>
                    <input type="text" class="form-control bg-light" value="Kasir Utama 01" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Modal Kas Awal (Rp)</label>
                    <input type="number" name="opening_balance" class="form-control" value="200000" min="0" step="1000" required>
                    <small class="text-muted">Contoh: Rp 200.000 untuk uang kembalian.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Buka Shift Kasir</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ITEM CUSTOM / UKURAN (UNTUK BANNER / A3+) -->
<div class="modal fade" id="modalItemConfig" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="itemConfigTitle">Spesifikasi Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cfgProductId">
                
                <div id="cfgDimensionsGroup" class="mb-3">
                    <label class="form-label fw-semibold">Ukuran Cetak (Centimeter)</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Lebar</span>
                                <input type="number" id="cfgWidth" class="form-control" value="200" min="10" oninput="recalcConfigItem()">
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Tinggi</span>
                                <input type="number" id="cfgHeight" class="form-control" value="100" min="10" oninput="recalcConfigItem()">
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-1">Luas: <span id="cfgAreaTxt" class="fw-bold">2.00</span> m²</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah (Kuantitas)</label>
                    <input type="number" id="cfgQty" class="form-control" value="1" min="1" oninput="recalcConfigItem()">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilihan Finishing Tambahan</label>
                    <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                        <?php foreach ($finishings as $fin): ?>
                        <div class="form-check py-1">
                            <input class="form-check-input cfg-fin-check" type="checkbox" value="<?= $fin['id'] ?>" 
                                   data-price="<?= $fin['selling_price'] ?>" data-type="<?= $fin['calculation_type'] ?>" data-name="<?= htmlspecialchars($fin['name']) ?>"
                                   id="finCheck<?= $fin['id'] ?>" onchange="recalcConfigItem()">
                            <label class="form-check-label d-flex justify-content-between small w-100" for="finCheck<?= $fin['id'] ?>">
                                <span><?= htmlspecialchars($fin['name']) ?></span>
                                <span class="text-primary fw-semibold">+<?= FormatHelper::rupiah($fin['selling_price']) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Estimasi Subtotal:</span>
                    <span id="cfgSubtotalTxt" class="fs-5 fw-bold text-primary">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="addConfiguredItemToCart()">
                    <i class="bi bi-cart-plus me-1"></i> Tambahkan ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PEMBAYARAN CHECKOUT -->
<div class="modal fade" id="modalPayment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i>Penyelesaian Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3 bg-light rounded mb-3 border">
                    <div class="text-muted small text-uppercase fw-semibold">Total Tagihan</div>
                    <div class="display-6 fw-bold text-dark mt-1" id="payModalTotal">Rp 0</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="payMethod" id="payMethodCash" value="cash" checked onchange="togglePayMethod()">
                        <label class="btn btn-outline-primary fw-semibold" for="payMethodCash"><i class="bi bi-cash me-1"></i> Tunai</label>

                        <input type="radio" class="btn-check" name="payMethod" id="payMethodBCA" value="bank_transfer" onchange="togglePayMethod()">
                        <label class="btn btn-outline-primary fw-semibold" for="payMethodBCA"><i class="bi bi-bank me-1"></i> Transfer Bank</label>

                        <input type="radio" class="btn-check" name="payMethod" id="payMethodQRIS" value="qris" onchange="togglePayMethod()">
                        <label class="btn btn-outline-primary fw-semibold" for="payMethodQRIS"><i class="bi bi-qr-code-scan me-1"></i> QRIS</label>
                    </div>
                </div>

                <div id="cashFields">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Uang Diterima (Rp)</label>
                        <input type="number" id="payCashReceived" class="form-control form-control-lg text-end fw-bold" oninput="calcChange()">
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="setCashExact()">Uang Pas</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="addCash(50000)">+50rb</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="addCash(100000)">+100rb</button>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold text-secondary">Kembalian:</span>
                        <span id="payChangeTxt" class="fs-4 fw-bold text-success">Rp 0</span>
                    </div>
                </div>

                <div id="bankFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Referensi Transfer / Bukti</label>
                        <input type="text" id="payBankRef" class="form-control" placeholder="Contoh: BCA-889912">
                    </div>
                </div>

                <div id="qrisFields" style="display: none;" class="text-center p-3 bg-light rounded border mb-3">
                    <div class="text-muted small mb-2"><i class="bi bi-qr-code fs-1 d-block mb-1 text-primary"></i> Scan QRIS Dinamis</div>
                    <span class="badge bg-success">QRIS Terhubung (Simulasi Aktif)</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small text-secondary">Catatan Pesanan (Opsional)</label>
                    <input type="text" id="payNotes" class="form-control form-control-sm" placeholder="Catatan khusus...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold px-4 py-2" id="btnSubmitPayment" onclick="processCheckout()">
                    <i class="bi bi-check2-circle me-1"></i> Bayar & Cetak Nota
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let appliedVoucher = null;
let currentConfigProduct = null;

// Filter and Search Products
document.getElementById('productSearch').addEventListener('input', filterProducts);
document.getElementById('categoryFilter').addEventListener('change', filterProducts);

function filterProducts() {
    const term = document.getElementById('productSearch').value.toLowerCase();
    const cat = document.getElementById('categoryFilter').value;
    const items = document.querySelectorAll('.product-item');

    items.forEach(el => {
        const itemCat = el.getAttribute('data-category');
        const itemName = el.getAttribute('data-name');
        const matchCat = (cat === 'all' || itemCat === cat);
        const matchName = itemName.includes(term);
        el.style.display = (matchCat && matchName) ? 'block' : 'none';
    });
}

function handleProductClick(product) {
    if (product.pricing_mode === 'area_sqm' || product.pricing_mode === 'custom') {
        openConfigModal(product);
    } else {
        // Direct add item (sheet_a3, pcs, rim)
        addItemToCart({
            product_id: product.id,
            item_name: product.name,
            pricing_mode: product.pricing_mode,
            width_cm: 0,
            height_cm: 0,
            area_sqm: 0,
            quantity: 1,
            unit: (product.pricing_mode === 'sheet_a3') ? 'lembar' : 'pcs',
            unit_price: parseFloat(product.base_price),
            cost_estimate: parseFloat(product.base_price) * 0.45,
            subtotal: parseFloat(product.base_price),
            finishing_details: ''
        });
    }
}

function openConfigModal(product) {
    currentConfigProduct = product;
    document.getElementById('itemConfigTitle').innerText = 'Kustom: ' + product.name;
    document.getElementById('cfgProductId').value = product.id;
    document.getElementById('cfgQty').value = 1;
    
    // Check if area mode
    if (product.pricing_mode === 'area_sqm') {
        document.getElementById('cfgDimensionsGroup').style.display = 'block';
        document.getElementById('cfgWidth').value = 200;
        document.getElementById('cfgHeight').value = 100;
    } else {
        document.getElementById('cfgDimensionsGroup').style.display = 'none';
    }

    // Uncheck finishings
    document.querySelectorAll('.cfg-fin-check').forEach(c => c.checked = false);
    recalcConfigItem();
    new bootstrap.Modal(document.getElementById('modalItemConfig')).show();
}

function recalcConfigItem() {
    if (!currentConfigProduct) return;
    const mode = currentConfigProduct.pricing_mode;
    const baseRate = parseFloat(currentConfigProduct.base_price);
    const qty = Math.max(1, parseFloat(document.getElementById('cfgQty').value) || 1);

    let area = 0;
    let baseSubtotal = 0;

    if (mode === 'area_sqm') {
        const w = parseFloat(document.getElementById('cfgWidth').value) || 0;
        const h = parseFloat(document.getElementById('cfgHeight').value) || 0;
        area = (w * h) / 10000;
        document.getElementById('cfgAreaTxt').innerText = area.toFixed(2);
        baseSubtotal = baseRate * Math.max(1.0, area) * qty;
    } else {
        baseSubtotal = baseRate * qty;
    }

    let finTotal = 0;
    document.querySelectorAll('.cfg-fin-check:checked').forEach(cb => {
        const p = parseFloat(cb.getAttribute('data-price')) || 0;
        const type = cb.getAttribute('data-type');
        if (type === 'per_sqm') {
            finTotal += p * Math.max(1.0, area) * qty;
        } else if (type === 'per_pcs' || type === 'per_side') {
            finTotal += p * qty;
        } else {
            finTotal += p;
        }
    });

    const total = baseSubtotal + finTotal;
    document.getElementById('cfgSubtotalTxt').innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

function addConfiguredItemToCart() {
    if (!currentConfigProduct) return;
    const mode = currentConfigProduct.pricing_mode;
    const baseRate = parseFloat(currentConfigProduct.base_price);
    const qty = Math.max(1, parseFloat(document.getElementById('cfgQty').value) || 1);
    const w = (mode === 'area_sqm') ? (parseFloat(document.getElementById('cfgWidth').value) || 0) : 0;
    const h = (mode === 'area_sqm') ? (parseFloat(document.getElementById('cfgHeight').value) || 0) : 0;
    const area = (w * h) / 10000;

    let finNames = [];
    let finTotal = 0;
    document.querySelectorAll('.cfg-fin-check:checked').forEach(cb => {
        finNames.push(cb.getAttribute('data-name'));
        const p = parseFloat(cb.getAttribute('data-price')) || 0;
        const type = cb.getAttribute('data-type');
        if (type === 'per_sqm') finTotal += p * Math.max(1.0, area) * qty;
        else if (type === 'per_pcs' || type === 'per_side') finTotal += p * qty;
        else finTotal += p;
    });

    let baseSubtotal = (mode === 'area_sqm') ? (baseRate * Math.max(1.0, area) * qty) : (baseRate * qty);
    let subtotal = baseSubtotal + finTotal;

    addItemToCart({
        product_id: currentConfigProduct.id,
        item_name: currentConfigProduct.name,
        pricing_mode: mode,
        width_cm: w,
        height_cm: h,
        area_sqm: area,
        quantity: qty,
        unit: (mode === 'area_sqm') ? 'm2' : 'pcs',
        unit_price: Math.round(subtotal / qty),
        cost_estimate: subtotal * 0.40,
        subtotal: subtotal,
        finishing_details: finNames.join(', ')
    });

    bootstrap.Modal.getInstance(document.getElementById('modalItemConfig')).hide();
}

function addItemToCart(item) {
    cart.push(item);
    renderCart();
}

function removeCartItem(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function updateCartQty(idx, newQty) {
    newQty = Math.max(1, parseFloat(newQty) || 1);
    cart[idx].quantity = newQty;
    // recalc
    if (cart[idx].pricing_mode === 'area_sqm') {
        const baseRate = cart[idx].unit_price;
        cart[idx].subtotal = baseRate * newQty;
    } else {
        cart[idx].subtotal = cart[idx].unit_price * newQty;
    }
    renderCart();
}

function clearCart() {
    cart = [];
    appliedVoucher = null;
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartItems');
    const empty = document.getElementById('cartEmptyState');
    tbody.innerHTML = '';

    if (cart.length === 0) {
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
        cart.forEach((it, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="width: 50%;">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 170px;">${it.item_name}</div>
                    ${it.width_cm > 0 ? `<small class="text-muted">${it.width_cm}x${it.height_cm}cm (${it.area_sqm} m²)</small><br>` : ''}
                    ${it.finishing_details ? `<span class="badge bg-light text-secondary border small">${it.finishing_details}</span>` : ''}
                </td>
                <td style="width: 25%;">
                    <input type="number" class="form-control form-control-sm text-center px-1" value="${it.quantity}" min="1" 
                           onchange="updateCartQty(${idx}, this.value)" style="width: 55px;">
                </td>
                <td class="text-end fw-bold text-dark" style="width: 20%;">
                    Rp ${Math.round(it.subtotal).toLocaleString('id-ID')}
                </td>
                <td class="text-end" style="width: 5%;">
                    <button class="btn btn-sm btn-link text-danger p-0" onclick="removeCartItem(${idx})">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    calculateTotals();
}

function updateCustomerDiscount() {
    const sel = document.getElementById('customerSelect');
    const opt = sel.options[sel.selectedIndex];
    const tier = opt.getAttribute('data-tier') || 'regular';
    document.getElementById('tierBadge').innerText = tier.toUpperCase();
    calculateTotals();
}

function applyVoucher() {
    const code = document.getElementById('voucherCode').value.trim().toUpperCase();
    if (code === 'HEMAT10') {
        appliedVoucher = { code: 'HEMAT10', pct: 10 };
        alert('Voucher HEMAT10 (Diskon 10%) berhasil diterapkan!');
    } else if (code === 'CETAKBANNER') {
        appliedVoucher = { code: 'CETAKBANNER', fixed: 20000 };
        alert('Voucher CETAKBANNER (Potongan Rp 20.000) berhasil diterapkan!');
    } else {
        alert('Kode voucher tidak ditemukan atau sudah habis.');
        appliedVoucher = null;
    }
    calculateTotals();
}

let grandTotal = 0;

function calculateTotals() {
    let subtotal = cart.reduce((acc, it) => acc + (parseFloat(it.subtotal) || 0), 0);
    
    // Customer tier discount
    const sel = document.getElementById('customerSelect');
    const opt = sel ? sel.options[sel.selectedIndex] : null;
    const tierDiscPct = opt ? (parseFloat(opt.getAttribute('data-discount')) || 0) : 0;
    
    let discount = subtotal * (tierDiscPct / 100);

    // Voucher
    if (appliedVoucher) {
        if (appliedVoucher.pct) {
            discount += subtotal * (appliedVoucher.pct / 100);
        } else if (appliedVoucher.fixed) {
            discount += appliedVoucher.fixed;
        }
    }

    discount = Math.min(discount, subtotal);
    grandTotal = Math.max(0, subtotal - discount);

    document.getElementById('txtSubtotal').innerText = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
    document.getElementById('txtDiscount').innerText = '- Rp ' + Math.round(discount).toLocaleString('id-ID');
    document.getElementById('txtTotal').innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
}

function openPaymentModal() {
    if (cart.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }
    document.getElementById('payModalTotal').innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    document.getElementById('payCashReceived').value = grandTotal;
    calcChange();
    new bootstrap.Modal(document.getElementById('modalPayment')).show();
}

function togglePayMethod() {
    const isCash = document.getElementById('payMethodCash').checked;
    const isBCA = document.getElementById('payMethodBCA').checked;
    const isQRIS = document.getElementById('payMethodQRIS').checked;

    document.getElementById('cashFields').style.display = isCash ? 'block' : 'none';
    document.getElementById('bankFields').style.display = isBCA ? 'block' : 'none';
    document.getElementById('qrisFields').style.display = isQRIS ? 'block' : 'none';
}

function calcChange() {
    const received = parseFloat(document.getElementById('payCashReceived').value) || 0;
    const change = Math.max(0, received - grandTotal);
    document.getElementById('payChangeTxt').innerText = 'Rp ' + Math.round(change).toLocaleString('id-ID');
}

function setCashExact() {
    document.getElementById('payCashReceived').value = grandTotal;
    calcChange();
}

function addCash(amt) {
    let cur = parseFloat(document.getElementById('payCashReceived').value) || 0;
    document.getElementById('payCashReceived').value = cur + amt;
    calcChange();
}

function processCheckout() {
    const btn = document.getElementById('btnSubmitPayment');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses Transaksi...';

    const customerId = document.getElementById('customerSelect').value;
    const voucherCode = appliedVoucher ? appliedVoucher.code : '';
    const payMethod = document.querySelector('input[name="payMethod"]:checked').value;
    const cashReceived = parseFloat(document.getElementById('payCashReceived').value) || 0;
    const bankRef = document.getElementById('payBankRef').value;
    const notes = document.getElementById('payNotes').value;

    const payload = {
        _csrf_token: '<?= CSRF::getToken() ?>',
        customer_id: customerId,
        voucher_code: voucherCode,
        payment_method: payMethod,
        amount_paid: (payMethod === 'cash') ? Math.min(cashReceived, grandTotal) : grandTotal,
        bank_reference: bankRef,
        notes: notes,
        items: cart
    };

    const checkoutUrl = (window.APP_BASE_URL || '') + '/pos/checkout';
    fetch(checkoutUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Bayar & Cetak Nota';
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalPayment')).hide();
            clearCart();
            // Open print receipt in popup
            window.open(res.receipt_url, '_blank', 'width=450,height=600');
            window.location.href = (window.APP_BASE_URL || '') + '/orders/' + res.order_id;
        } else {
            alert('Error: ' + (res.error || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Bayar & Cetak Nota';
        alert('Gagal menghubungi server: ' + err);
    });
}
</script>
