<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="/purchasing" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Pembelian</a>
        <h3 class="fw-bold mb-1">Master Pemasok (Supplier)</h3>
        <p class="text-muted small mb-0">Kelola daftar rekanan vendor kertas, flexy, tinta, sparepart, dan perlengkapan finishing.</p>
    </div>
    <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSupplier">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Supplier Baru
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Kode & Nama Supplier</th>
                        <th>Kontak PIC</th>
                        <th>Telepon & Email</th>
                        <th>Alamat</th>
                        <th>Termin Bayar (Tempo)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $sp): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($sp['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($sp['code']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($sp['contact_person'] ?? '-') ?></td>
                        <td>
                            <div><i class="bi bi-telephone text-primary me-1"></i><?= htmlspecialchars($sp['phone']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($sp['email'] ?? '-') ?></small>
                        </td>
                        <td><small><?= htmlspecialchars($sp['address'] ?? '-') ?></small></td>
                        <td><span class="badge bg-light text-dark border"><?= $sp['payment_terms_days'] ?> Hari</span></td>
                        <td><span class="badge bg-success">Aktif</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH SUPPLIER -->
<div class="modal fade" id="modalAddSupplier" tabindex="-1">
    <div class="modal-dialog">
        <form action="/suppliers/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Supplier Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Perusahaan / Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: PT Sumber Bahan Grafika" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Kontak Person (PIC)</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="Nama sales / marketing">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="021-xxxx / 08xxxx" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="sales@vendor.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Termin Pembayaran (Hari)</label>
                    <input type="number" name="payment_terms_days" class="form-control" value="30" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Kantor / Gudang Supplier</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>
