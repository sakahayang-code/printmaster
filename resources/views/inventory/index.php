<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Persediaan Bahan Baku & Gudang</h3>
        <p class="text-muted small mb-0">Pantau ketersediaan stok fisik bahan (kertas, flexy, vinyl, tinta), nilai rata-rata HPP, dan ambang batas reorder.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/inventory/transfers" class="btn btn-outline-warning text-dark btn-sm">
            <i class="bi bi-arrow-left-right me-1"></i> Transfer Stok Cabang
        </a>
        <a href="/inventory/movements" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-journal-text me-1"></i> Kartu Stok (Mutasi)
        </a>
        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMaterial">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Bahan Baku
        </button>
    </div>
</div>

<!-- FILTER -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form action="/inventory" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama bahan baku / kode..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori Bahan</option>
                    <option value="banner_flexy" <?= $category === 'banner_flexy' ? 'selected' : '' ?>>Banner & Flexy</option>
                    <option value="paper" <?= $category === 'paper' ? 'selected' : '' ?>>Kertas & Lembaran Plano</option>
                    <option value="sticker_vinyl" <?= $category === 'sticker_vinyl' ? 'selected' : '' ?>>Stiker & Vinyl</option>
                    <option value="ink_toner" <?= $category === 'ink_toner' ? 'selected' : '' ?>>Tinta & Toner</option>
                    <option value="finishing_supplies" <?= $category === 'finishing_supplies' ? 'selected' : '' ?>>Finishing & Aksesoris</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL BAHAN BAKU -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Kode & Nama Bahan</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok Minimum</th>
                        <th>Stok Saat Ini</th>
                        <th>Status Stok</th>
                        <th>Harga Rata-Rata (HPP)</th>
                        <th>Nilai Total Aset</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $mat): 
                        $isLow = ($mat['current_stock'] <= $mat['min_stock']);
                        $totalVal = (float)$mat['current_stock'] * (float)$mat['avg_cost'];
                    ?>
                    <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($mat['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($mat['code']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-secondary border"><?= strtoupper(str_replace('_', ' ', $mat['category'])) ?></span></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($mat['unit']) ?></span></td>
                        <td><?= number_format($mat['min_stock'], 1) ?> <?= htmlspecialchars($mat['unit']) ?></td>
                        <td class="fw-bold fs-6 <?= $isLow ? 'text-danger' : 'text-dark' ?>">
                            <?= number_format($mat['current_stock'], 1) ?> <?= htmlspecialchars($mat['unit']) ?>
                        </td>
                        <td>
                            <?php if ($isLow): ?>
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Stok Menipis</span>
                            <?php else: ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Aman</span>
                            <?php endif; ?>
                        </td>
                        <td><?= FormatHelper::rupiah($mat['avg_cost']) ?></td>
                        <td class="fw-bold text-dark"><?= FormatHelper::rupiah($totalVal) ?></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/inventory/movements?material_id=<?= $mat['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Kartu Stok">
                                    <i class="bi bi-journal-text"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAdjustModal(<?= $mat['id'] ?>, '<?= htmlspecialchars($mat['name']) ?>', <?= $mat['current_stock'] ?>, '<?= htmlspecialchars($mat['unit']) ?>')" title="Penyesuaian Opname">
                                    <i class="bi bi-sliders"></i> Opname
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH BAHAN BAKU -->
<div class="modal fade" id="modalAddMaterial" tabindex="-1">
    <div class="modal-dialog">
        <form action="/inventory/materials/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Bahan Baku Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan Baku <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Kertas Ivory 300g Plano" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="banner_flexy">Banner & Flexy</option>
                            <option value="paper">Kertas & Karton</option>
                            <option value="sticker_vinyl">Stiker & Vinyl</option>
                            <option value="ink_toner">Tinta & Toner</option>
                            <option value="finishing_supplies">Finishing & Aksesoris</option>
                            <option value="sparepart">Sparepart Mesin</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Satuan Ukuran</label>
                        <input type="text" name="unit" class="form-control" placeholder="m2 / lembar / rol / liter / pcs" required value="lembar">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Stok Minimum (Reorder Point)</label>
                        <input type="number" name="min_stock" class="form-control" value="20" min="0" step="0.5">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Stok Awal Fisik</label>
                        <input type="number" name="initial_stock" class="form-control" value="100" min="0" step="0.5">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga Modal Satuan (HPP Rp)</label>
                    <input type="number" name="avg_cost" class="form-control" value="2500" min="0" step="100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Bahan Baku</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PENYESUAIAN STOK (OPNAME) -->
<div class="modal fade" id="modalAdjustStock" tabindex="-1">
    <div class="modal-dialog">
        <form action="/inventory/adjust" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <input type="hidden" name="material_id" id="adjMaterialId">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Penyesuaian Stok Fisik (Stock Opname)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="fw-bold text-dark fs-6" id="adjMaterialName">Bahan: -</div>
                    <div class="text-muted small">Stok Tercatat Sistem: <strong id="adjCurrentStockTxt">0</strong></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Hasil Hitung Fisik Sebenarnya</label>
                    <div class="input-group">
                        <input type="number" name="physical_stock" id="adjPhysicalStock" class="form-control form-control-lg fw-bold" step="0.01" required>
                        <span class="input-group-text fw-bold" id="adjUnitTxt">pcs</span>
                    </div>
                    <small class="text-muted">Sistem akan otomatis menghitung selisih dan mencatat transaksi penyesuaian di kartu stok.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Penyesuaian</label>
                    <input type="text" name="adjustment_reason" class="form-control" placeholder="Contoh: Hasil Stock Opname akhir bulan / bahan sobek" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning fw-bold">Sesuaikan Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdjustModal(id, name, curStock, unit) {
    document.getElementById('adjMaterialId').value = id;
    document.getElementById('adjMaterialName').innerText = 'Bahan: ' + name;
    document.getElementById('adjCurrentStockTxt').innerText = curStock + ' ' + unit;
    document.getElementById('adjPhysicalStock').value = curStock;
    document.getElementById('adjUnitTxt').innerText = unit;
    new bootstrap.Modal(document.getElementById('modalAdjustStock')).show();
}
</script>
