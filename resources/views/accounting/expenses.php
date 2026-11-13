<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2 text-primary"></i>Biaya & Kas Keluar (Expenses)</h4>
        <p class="text-muted small mb-0">Pencatatan pengeluaran operasional cabang (listrik, maintenance, ATK, konsumsi, bensin) terintegrasi otomatis ke jurnal.</p>
    </div>
    <div class="col-auto d-flex gap-2">
        <div class="card bg-danger text-white px-3 py-2 border-0 shadow-sm rounded-3">
            <small class="opacity-75">Pengeluaran Bulan Ini</small>
            <div class="fw-bold fs-5"><?= FormatHelper::rupiah($totalMonth) ?></div>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-lg me-1"></i> Catat Biaya Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Pengeluaran</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>Kategori / Akun Biaya</th>
                    <th>Dibayarkan Kepada</th>
                    <th>Keterangan</th>
                    <th>Metode</th>
                    <th class="text-end">Nominal</th>
                    <th>Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada catatan pengeluaran biaya.</td>
                </tr>
                <?php else: foreach ($expenses as $e): ?>
                <tr>
                    <td><strong class="text-primary"><?= htmlspecialchars($e['expense_number']) ?></strong></td>
                    <td class="small"><?= FormatHelper::date($e['expense_date']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($e['branch_name']) ?></span></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($e['category']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($e['account_name'] ?? 'Beban Operasional') ?></div>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($e['paid_to']) ?></td>
                    <td class="small text-muted" style="max-width: 250px;"><?= htmlspecialchars($e['description']) ?></td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            <?= $e['payment_method'] === 'bank_transfer' ? 'Transfer Bank' : 'Kas Tunai' ?>
                        </span>
                    </td>
                    <td class="text-end fw-bold text-danger fs-6"><?= FormatHelper::rupiah($e['amount']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($e['user_name']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/expenses/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2 text-danger"></i>Catat Pengeluaran Kas/Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Biaya</label>
                        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Kas Tunai (Petty Cash)</option>
                            <option value="bank_transfer">Transfer Bank</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kategori Biaya</label>
                    <input type="text" name="category" class="form-control" placeholder="Contoh: Listrik & Air, Konsumsi Lembur, Bensin, Sparepart" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Akun Beban (Chart of Accounts)</label>
                    <select name="account_id" class="form-select" required>
                        <?php foreach ($expenseAccounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['code']) ?> - <?= htmlspecialchars($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nominal Pengeluaran</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="amount" class="form-control" placeholder="0" min="1000" step="500" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dibayarkan Kepada (Penerima)</label>
                    <input type="text" name="paid_to" class="form-control" placeholder="Contoh: PLN, Teknisi Mesin, Warung Barokah" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi / Keterangan</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Tujuan pengeluaran dana..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan & Posting Jurnal</button>
            </div>
        </form>
    </div>
</div>
