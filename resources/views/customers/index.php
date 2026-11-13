<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Pelanggan & CRM</h3>
        <p class="text-muted small mb-0">Kelola database pelanggan individu & perusahaan, tier loyalitas, dan limit kredit.</p>
    </div>
    <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddCustomer">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pelanggan Baru
    </button>
</div>

<!-- FILTER -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form action="/customers" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama pelanggan, nomor HP, perusahaan..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="tier" class="form-select form-select-sm">
                    <option value="">Semua Kategori (Tier)</option>
                    <option value="regular" <?= $tier === 'regular' ? 'selected' : '' ?>>Regular</option>
                    <option value="member" <?= $tier === 'member' ? 'selected' : '' ?>>Member</option>
                    <option value="reseller" <?= $tier === 'reseller' ? 'selected' : '' ?>>Reseller</option>
                    <option value="corporate" <?= $tier === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="/customers" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- DAFTAR PELANGGAN -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Kode & Nama Pelanggan</th>
                        <th>Kontak & Alamat</th>
                        <th>Kategori Tier</th>
                        <th>Diskon Khusus</th>
                        <th>Total Belanja</th>
                        <th>Saldo Piutang</th>
                        <th>Poin Reward</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Belum ada data pelanggan yang sesuai.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>
                                <a href="/customers/<?= $c['id'] ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($c['name']) ?>
                                </a>
                                <div class="text-xs text-muted">
                                    <?= htmlspecialchars($c['customer_code']) ?> <?= $c['company_name'] ? "• " . htmlspecialchars($c['company_name']) : '' ?>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($c['phone']) ?></div>
                                <?php if (!empty($c['email'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $tierBadges = [
                                    'regular'   => 'bg-secondary',
                                    'member'    => 'bg-info text-dark',
                                    'reseller'  => 'bg-warning text-dark',
                                    'corporate' => 'bg-primary'
                                ];
                                $tb = $tierBadges[$c['customer_tier']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $tb ?>"><?= strtoupper($c['customer_tier']) ?></span>
                            </td>
                            <td>
                                <?= ($c['discount_percent'] > 0) ? "<span class=\"badge bg-success\">{$c['discount_percent']}%</span>" : '-' ?>
                            </td>
                            <td class="fw-bold text-dark"><?= FormatHelper::rupiah($c['total_spend']) ?></td>
                            <td>
                                <?php if ($c['current_receivable'] > 0): ?>
                                    <span class="text-danger fw-bold"><?= FormatHelper::rupiah($c['current_receivable']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= number_format($c['loyalty_points']) ?> Poin</span></td>
                            <td class="text-end">
                                <a href="/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail CRM">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PELANGGAN -->
<div class="modal fade" id="modalAddCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="/customers/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Data Pelanggan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipe Pelanggan</label>
                        <select name="type" class="form-select">
                            <option value="individual">Individu / Perorangan</option>
                            <option value="company">Perusahaan / Korporat</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap / Kontak Person <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Nama pelanggan...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Perusahaan / Instansi (Opsional)</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Contoh: PT Sumber Makmur">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@domain.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori / Tier Membership</label>
                        <select name="customer_tier" class="form-select">
                            <option value="regular">Reguler (Tanpa Diskon)</option>
                            <option value="member">Member Loyal (Diskon Otomatis)</option>
                            <option value="reseller">Reseller Percetakan (Grosir)</option>
                            <option value="corporate">Corporate / Lembaga</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Diskon Khusus (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="0" min="0" max="50" step="0.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Batas Kredit / Piutang (Limit Rp)</label>
                        <input type="number" name="credit_limit" class="form-control" value="0" min="0" step="500000">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Alamat Pengiriman / Domisili</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Catatan Pelanggan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Preferensi warna, syarat faktur, dsb...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>
