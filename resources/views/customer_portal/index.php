<?php
use App\Helpers\FormatHelper;
?>

<!-- Hero Banner -->
<section class="hero-banner mb-5">
    <div class="container text-center py-4">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
            <i class="bi bi-stars me-1"></i> Solusi Percetakan Cepat, Presisi & Bergaransi
        </span>
        <h1 class="display-5 fw-bold mb-3">Pesan Cetak & Lacak Status Tanpa Ribet</h1>
        <p class="lead text-white-50 max-w-600 mx-auto mb-4" style="max-width: 650px;">
            PRINTMASTER hadir dengan mesin cetak modern indoor & outdoor, standar Quality Control ketat, dan dukungan banyak cabang di kota Anda.
        </p>

        <!-- Quick Tracking Form in Hero -->
        <div class="card border-0 shadow-lg mx-auto p-2 rounded-4" style="max-width: 550px;" id="tracking-section">
            <form method="GET" action="/customer-portal/track" class="row g-2 p-1">
                <div class="col-sm-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" name="q" class="form-control border-0 shadow-none ps-0" placeholder="Nomor Pesanan (ORD-...) atau No. WA" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                        Lacak Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Features / Keunggulan -->
<div class="container mb-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-primary mb-3"><i class="bi bi-lightning-charge-fill fs-1"></i></div>
                <h5 class="fw-bold">Pengerjaan Ekspres</h5>
                <p class="text-muted small mb-0">Antrean produksi digital yang termonitor real-time memastikan hasil cetak Anda selesai tepat waktu.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-success mb-3"><i class="bi bi-patch-check-fill fs-1"></i></div>
                <h5 class="fw-bold">Jaminan Mutu (QC Ketat)</h5>
                <p class="text-muted small mb-0">Setiap cetakan melewati tahapan inspeksi visual warna, dimensi, dan finishing sebelum diserahkan.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="text-info mb-3"><i class="bi bi-buildings-fill fs-1"></i></div>
                <h5 class="fw-bold">Jaringan Multi-Cabang</h5>
                <p class="text-muted small mb-0">Pesan dari mana saja, ambil di cabang terdekat atau kirim langsung ke alamat Anda dengan aman.</p>
            </div>
        </div>
    </div>
</div>

<!-- Product Catalog Highlights -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Katalog Produk Percetakan</h4>
            <p class="text-muted small mb-0">Pilihan produk cetak populer siap dipesan langsung.</p>
        </div>
        <a href="/customer-portal/order" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
            <i class="bi bi-cart-plus me-1"></i> Pesan Sekarang
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-light p-4 text-center border-bottom">
                    <i class="bi bi-file-earmark-richtext text-primary fs-1"></i>
                </div>
                <div class="card-body p-3 d-flex flex-column">
                    <span class="badge bg-secondary-subtle text-secondary small mb-2 align-self-start">
                        <?= htmlspecialchars($p['category_name']) ?>
                    </span>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                    <p class="text-muted small flex-grow-1" style="min-height: 38px;"><?= htmlspecialchars($p['description'] ?? 'Layanan cetak berkualitas tinggi.') ?></p>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Mulai dari</small>
                            <strong class="text-primary"><?= FormatHelper::rupiah($p['base_price']) ?></strong>
                            <small class="text-muted">/ <?= $p['pricing_type'] === 'area' ? 'm²' : 'pcs' ?></small>
                        </div>
                        <a href="/customer-portal/order?product_id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm rounded-circle" title="Pesan Ini">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Cabang Kami -->
<div class="container mb-5">
    <div class="card bg-light border-0 p-4 rounded-4">
        <h5 class="fw-bold text-center mb-4"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Cabang Operasional PRINTMASTER</h5>
        <div class="row g-3">
            <?php foreach ($branches as $b): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-3 h-100">
                    <div class="fw-bold text-primary mb-1"><?= htmlspecialchars($b['name']) ?></div>
                    <p class="small text-muted mb-2"><?= htmlspecialchars($b['address']) ?></p>
                    <div class="small fw-semibold text-secondary">
                        <i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($b['phone']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
