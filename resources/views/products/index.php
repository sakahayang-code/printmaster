<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Katalog Produk & Layanan Percetakan</h3>
        <p class="text-muted small mb-0">Atur produk cetak, skema perhitungan harga (m², A3+, rim, pcs), dan opsi finishing.</p>
    </div>
    <?php if (Auth::hasPermission('catalog.manage')): ?>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddFinishing">
            <i class="bi bi-scissors me-1"></i> Tambah Finishing
        </button>
        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Produk Baru
        </button>
    </div>
    <?php endif; ?>
</div>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="pills-products-tab" data-bs-toggle="pill" data-bs-target="#pills-products" type="button" role="tab">
            <i class="bi bi-grid me-1"></i> Daftar Produk (<?= count($products) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="pills-finishings-tab" data-bs-toggle="pill" data-bs-target="#pills-finishings" type="button" role="tab">
            <i class="bi bi-scissors me-1"></i> Opsi Finishing (<?= count($finishings) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="pills-tabContent">
    <!-- TAB 1: PRODUK -->
    <div class="tab-pane fade show active" id="pills-products" role="tabpanel">
        <div class="card card-custom mb-3">
            <div class="card-body p-3">
                <form action="/products" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama produk atau kode..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Kode & Nama Produk</th>
                                <th>Kategori</th>
                                <th>Metode Hitung</th>
                                <th>Harga Dasar</th>
                                <th>Batas Min. Harga</th>
                                <th>Margin Default</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $pr): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($pr['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($pr['code']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($pr['category_name']) ?></span></td>
                                <td><span class="badge bg-primary-subtle text-primary border"><?= strtoupper($pr['pricing_mode']) ?></span></td>
                                <td class="fw-bold text-dark"><?= FormatHelper::rupiah($pr['base_price']) ?></td>
                                <td><?= FormatHelper::rupiah($pr['min_price']) ?></td>
                                <td><?= $pr['default_margin_percent'] ?>%</td>
                                <td class="text-end">
                                    <form action="/products/<?= $pr['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan produk ini?')">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Nonaktifkan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: FINISHING -->
    <div class="tab-pane fade" id="pills-finishings" role="tabpanel">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Kode & Nama Finishing</th>
                                <th>Tipe Perhitungan</th>
                                <th>Harga Modal (HPP)</th>
                                <th>Harga Jual ke Pelanggan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($finishings as $fn): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($fn['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($fn['code']) ?></small>
                                </td>
                                <td><span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', $fn['calculation_type'])) ?></span></td>
                                <td class="text-muted"><?= FormatHelper::rupiah($fn['cost_price']) ?></td>
                                <td class="fw-bold text-primary"><?= FormatHelper::rupiah($fn['selling_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PRODUK -->
<div class="modal fade" id="modalAddProduct" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="/products/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Produk Cetak Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Cetak Brosur A4 Art Paper 150g" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Skema Perhitungan Harga</label>
                        <select name="pricing_mode" class="form-select">
                            <option value="area_sqm">Per Meter Persegi (m²) - Banner</option>
                            <option value="sheet_a3">Per Lembar A3+ - Laser Digital</option>
                            <option value="pcs">Per Pcs / Box / Unit</option>
                            <option value="rim">Per Rim (500 Lembar)</option>
                            <option value="custom">Formula Kustom Komponen</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Harga Dasar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="base_price" class="form-control" min="0" step="100" required placeholder="Tarif satuan">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Batas Minimum Harga (Rp)</label>
                        <input type="number" name="min_price" class="form-control" min="0" step="100" placeholder="Minimum charge">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Margin Laba Target (%)</label>
                        <input type="number" name="default_margin_percent" class="form-control" value="35" min="0" max="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Toleransi Waste (%)</label>
                        <input type="number" name="waste_percent" class="form-control" value="5" min="0" max="20">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Deskripsi & Spesifikasi Produk</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan bahan, gramatur, ketahanan..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH FINISHING -->
<div class="modal fade" id="modalAddFinishing" tabindex="-1">
    <div class="modal-dialog">
        <form action="/products/finishings/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Tambah Opsi Finishing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Finishing</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Jilid Hardcover Lux" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipe Perhitungan</label>
                    <select name="calculation_type" class="form-select">
                        <option value="per_pcs">Per Pcs / Unit</option>
                        <option value="per_sqm">Per Meter Persegi (m²)</option>
                        <option value="per_side">Per Sisi (Laminating)</option>
                        <option value="fixed">Biaya Tetap (Fixed Fee)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Biaya Modal HPP (Rp)</label>
                    <input type="number" name="cost_price" class="form-control" value="0" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga Jual ke Pelanggan (Rp)</label>
                    <input type="number" name="selling_price" class="form-control" value="5000" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Finishing</button>
            </div>
        </form>
    </div>
</div>
