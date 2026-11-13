<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="/customers" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pelanggan</a>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($customer['name']) ?></h3>
            <span class="badge bg-primary text-uppercase"><?= $customer['customer_tier'] ?></span>
        </div>
        <div class="text-muted small mt-1">Kode: <strong><?= htmlspecialchars($customer['customer_code']) ?></strong></div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditCustomer">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </button>
        <a href="/orders/create" class="btn btn-primary btn-sm">
            <i class="bi bi-cart-plus me-1"></i> Buat Order Pelanggan Ini
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Kontak & WhatsApp</span>
            <div class="fw-bold fs-6 mt-1"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($customer['phone']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($customer['email'] ?? '-') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Poin Reward</span>
            <div class="fw-bold fs-4 text-warning mt-1"><i class="bi bi-star-fill me-1"></i><?= number_format($customer['loyalty_points']) ?></div>
            <div class="text-muted small">Diskon Tier: <?= $customer['discount_percent'] ?>%</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Saldo Piutang Saat Ini</span>
            <div class="fw-bold fs-4 text-danger mt-1"><?= FormatHelper::rupiah($customer['current_receivable']) ?></div>
            <div class="text-muted small">Limit Kredit: <?= FormatHelper::rupiah($customer['credit_limit']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Alamat Domisili</span>
            <div class="small text-dark mt-1"><?= htmlspecialchars($customer['address'] ?? 'Belum ada alamat') ?></div>
        </div>
    </div>
</div>

<!-- RIWAYAT TRANSAKSI ORDER -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pesanan Pelanggan (<?= count($orders) ?>)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>No. Order</th>
                        <th>Cabang</th>
                        <th>Tanggal</th>
                        <th>Total Belanja</th>
                        <th>Status Pembayaran</th>
                        <th>Status Order</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Pelanggan ini belum pernah melakukan pemesanan.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a href="/orders/<?= $o['id'] ?>" class="fw-bold"><?= htmlspecialchars($o['order_number']) ?></a></td>
                            <td><?= htmlspecialchars($o['branch_name']) ?></td>
                            <td><?= FormatHelper::tanggal($o['order_date'], false) ?></td>
                            <td class="fw-bold text-dark"><?= FormatHelper::rupiah($o['total_amount']) ?></td>
                            <td><?= FormatHelper::badgeStatusPayment($o['payment_status']) ?></td>
                            <td><?= FormatHelper::badgeStatusOrder($o['status']) ?></td>
                            <td class="text-end">
                                <a href="/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT CUSTOMER -->
<div class="modal fade" id="modalEditCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="/customers/<?= $customer['id'] ?>/update" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Edit Data Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Perusahaan</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($customer['company_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tier Membership</label>
                        <select name="customer_tier" class="form-select">
                            <option value="regular" <?= $customer['customer_tier'] === 'regular' ? 'selected' : '' ?>>Regular</option>
                            <option value="member" <?= $customer['customer_tier'] === 'member' ? 'selected' : '' ?>>Member</option>
                            <option value="reseller" <?= $customer['customer_tier'] === 'reseller' ? 'selected' : '' ?>>Reseller</option>
                            <option value="corporate" <?= $customer['customer_tier'] === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Diskon Khusus (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="<?= $customer['discount_percent'] ?>" min="0" max="50">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
