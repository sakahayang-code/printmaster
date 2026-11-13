<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="container py-4">
    <div class="mb-4">
        <a href="/customer-portal" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
        <h4 class="fw-bold mt-2 mb-1"><i class="bi bi-cart-plus text-primary me-2"></i>Formulir Pemesanan Cetak Online</h4>
        <p class="text-muted small">Isi formulir di bawah ini untuk memesan cetak langsung. Tim kami akan segera memverifikasi file dan memproses pesanan Anda.</p>
    </div>

    <form method="POST" action="/customer-portal/order/store" class="row g-4">
        <?= CSRF::field() ?>

        <!-- Kolom Kiri: Data Pemesan & Lokasi -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-person me-2 text-primary"></i>1. Informasi Pemesan</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Cabang Terdekat</label>
                    <select name="branch_id" class="form-select" required>
                        <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['city'] ?? 'Kota') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap Anda / Perusahaan</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Contoh: Budi Santoso / CV Maju Mundur" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. WhatsApp Aktif</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="08123456789" required>
                        <small class="text-muted">Notifikasi status dikirim ke nomor ini.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email (Opsional)</label>
                        <input type="email" name="customer_email" class="form-control" placeholder="email@gmail.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap (Jika Dikirim)</label>
                    <textarea name="customer_address" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah/kantor, kecamatan, kota..."></textarea>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Spesifikasi Cetak & Kalkulasi -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-printer me-2 text-primary"></i>2. Spesifikasi Produk Cetak</h6>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Produk Cetak</label>
                    <select name="product_id" id="prodSelect" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" 
                                data-type="<?= $p['pricing_type'] ?>" 
                                data-price="<?= $p['base_price'] ?>"
                                <?= isset($_GET['product_id']) && $_GET['product_id'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category_name']) ?>) - <?= FormatHelper::rupiah($p['base_price']) ?>/<?= $p['pricing_type'] === 'area' ? 'm²' : 'pcs' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dimension inputs for area pricing -->
                <div id="dimGroup" class="row g-2 mb-3" style="display: none;">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Lebar (cm)</label>
                        <input type="number" name="width" id="inpWidth" class="form-control" placeholder="100" min="1" step="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Panjang (cm)</label>
                        <input type="number" name="length" id="inpLength" class="form-control" placeholder="200" min="1" step="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Pesanan (Qty)</label>
                    <input type="number" name="quantity" id="inpQty" class="form-control" value="1" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Link File Desain (Google Drive / Dropbox / WeTransfer)</label>
                    <input type="url" name="notes" class="form-control" placeholder="https://drive.google.com/..." required>
                    <small class="text-muted">Pastikan izin akses link dibuka (Siapa saja yang memiliki link dapat melihat).</small>
                </div>

                <!-- Estimasi Harga Box -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Estimasi Biaya:</span>
                        <h4 class="fw-bold mb-0 text-primary" id="estPriceDisplay">Rp 0</h4>
                    </div>
                    <small class="text-muted d-block mt-1">*Harga final dapat disesuaikan tim cabang setelah file diperiksa.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                    <i class="bi bi-send-check me-1"></i> Kirim Pesanan Sekarang
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prodSelect = document.getElementById('prodSelect');
    const dimGroup = document.getElementById('dimGroup');
    const inpWidth = document.getElementById('inpWidth');
    const inpLength = document.getElementById('inpLength');
    const inpQty = document.getElementById('inpQty');
    const estDisplay = document.getElementById('estPriceDisplay');

    function calculate() {
        const opt = prodSelect.options[prodSelect.selectedIndex];
        if (!opt || !opt.value) {
            estDisplay.innerText = 'Rp 0';
            dimGroup.style.display = 'none';
            return;
        }

        const type = opt.getAttribute('data-type');
        const price = parseFloat(opt.getAttribute('data-price') || 0);
        const qty = parseFloat(inpQty.value || 1);

        if (type === 'area') {
            dimGroup.style.display = 'flex';
            const w = parseFloat(inpWidth.value || 0);
            const l = parseFloat(inpLength.value || 0);
            const areaM2 = (w * l) / 10000;
            const total = Math.max(0, areaM2 * price * qty);
            estDisplay.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        } else {
            dimGroup.style.display = 'none';
            const total = price * qty;
            estDisplay.innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        }
    }

    prodSelect.addEventListener('change', calculate);
    inpWidth.addEventListener('input', calculate);
    inpLength.addEventListener('input', calculate);
    inpQty.addEventListener('input', calculate);

    calculate();
});
</script>
