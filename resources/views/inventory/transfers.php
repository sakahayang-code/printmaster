<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="/inventory" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Gudang</a>
        <h3 class="fw-bold mb-1">Transfer Stok Antar-Cabang</h3>
        <p class="text-muted small mb-0">Kelola perpindahan stok bahan baku antar cabang dengan alur pengiriman dan penerimaan yang aman.</p>
    </div>
    <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateTransfer">
        <i class="bi bi-send-plus-fill me-1"></i> Buat Transfer Stok Baru
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>No. Transfer</th>
                        <th>Cabang Pengirim</th>
                        <th>Cabang Penerima</th>
                        <th>Tanggal Kirim</th>
                        <th>Status Pengiriman</th>
                        <th>Keterangan</th>
                        <th>Pemohon</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transfers)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada aktivitas transfer bahan antar-cabang.</td></tr>
                    <?php else: ?>
                        <?php foreach ($transfers as $tr): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($tr['transfer_number']) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($tr['from_branch_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($tr['from_warehouse_name']) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($tr['to_branch_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($tr['to_warehouse_name']) ?></small>
                            </td>
                            <td><?= FormatHelper::tanggal($tr['transfer_date'], false) ?></td>
                            <td>
                                <?php if ($tr['status'] === 'received'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Diterima Lengkap</span>
                                <?php elseif ($tr['status'] === 'shipped'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-truck me-1"></i>Dalam Perjalanan (In-Transit)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($tr['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($tr['notes'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tr['requester_name']) ?></td>
                            <td class="text-end">
                                <?php if ($tr['status'] === 'shipped' && $tr['to_branch_id'] == Auth::activeBranchId()): ?>
                                <form action="/inventory/transfers/<?= $tr['id'] ?>/receive" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi penerimaan barang di gudang tujuan?')">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                                        <i class="bi bi-box-seam me-1"></i> Terima Barang
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BUAT TRANSFER STOK -->
<div class="modal fade" id="modalCreateTransfer" tabindex="-1">
    <div class="modal-dialog">
        <form action="/inventory/transfers/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Kirim Transfer Bahan ke Cabang Lain</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Cabang Tujuan</label>
                    <select name="to_branch_id" class="form-select" required>
                        <?php foreach ($branches as $br): ?>
                        <?php if ($br['id'] != Auth::activeBranchId()): ?>
                        <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?> (<?= htmlspecialchars($br['code']) ?>)</option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Gudang Asal</label>
                        <select name="from_warehouse_id" class="form-select">
                            <?php foreach ($warehouses as $wh): ?>
                            <?php if ($wh['branch_id'] == Auth::activeBranchId()): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Gudang Tujuan</label>
                        <select name="to_warehouse_id" class="form-select">
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Bahan Baku</label>
                    <select name="material_id" class="form-select" required>
                        <?php foreach ($materials as $mat): ?>
                        <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['name']) ?> (Stok: <?= $mat['current_stock'] ?> <?= $mat['unit'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah yang Dikirim</label>
                    <input type="number" name="quantity" class="form-control" step="0.5" min="1" required placeholder="Jumlah fisik yang dikirim">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Pengiriman</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Bantuan bahan banner cabang Surabaya">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Kirim Transfer</button>
            </div>
        </form>
    </div>
</div>
