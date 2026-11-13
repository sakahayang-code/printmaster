<?php use App\Helpers\FormatHelper; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="/inventory" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Gudang</a>
        <h3 class="fw-bold mb-1">Kartu Stok & Riwayat Mutasi</h3>
        <p class="text-muted small mb-0">Ledger mutasi persediaan immutable (tidak dapat dihapus). Mencatat keluar-masuk barang secara kronologis.</p>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form action="/inventory/movements" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <select name="material_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Bahan Baku --</option>
                    <?php foreach ($materials as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $materialId == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <a href="/inventory/movements" class="btn btn-sm btn-outline-secondary w-100">Tampilkan Semua Mutasi</a>
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
                        <th>Waktu & Tanggal</th>
                        <th>Bahan Baku</th>
                        <th>Gudang Cabang</th>
                        <th>Jenis Mutasi</th>
                        <th>Jumlah Masuk/Keluar</th>
                        <th>Saldo Akhir</th>
                        <th>Referensi & Keterangan</th>
                        <th>Pelaku</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada riwayat mutasi stok untuk bahan ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movements as $mv): 
                            $isPos = in_array($mv['movement_type'], ['purchase_in', 'transfer_in', 'return_in']);
                        ?>
                        <tr>
                            <td><?= FormatHelper::tanggal($mv['created_at']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($mv['material_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($mv['material_code']) ?></small>
                            </td>
                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($mv['warehouse_name']) ?></span></td>
                            <td>
                                <?php
                                $mvTypes = [
                                    'purchase_in'    => ['bg-success', 'Pembelian Masuk'],
                                    'production_out' => ['bg-danger', 'Pemakaian Produksi'],
                                    'transfer_in'    => ['bg-info text-dark', 'Transfer Masuk'],
                                    'transfer_out'   => ['bg-warning text-dark', 'Transfer Keluar'],
                                    'adjustment'     => ['bg-secondary', 'Penyesuaian Opname']
                                ];
                                $mt = $mvTypes[$mv['movement_type']] ?? ['bg-secondary', $mv['movement_type']];
                                ?>
                                <span class="badge <?= $mt[0] ?>"><?= $mt[1] ?></span>
                            </td>
                            <td class="fw-bold fs-6 <?= $isPos ? 'text-success' : 'text-danger' ?>">
                                <?= $isPos ? '+' : '-' ?><?= number_format(abs($mv['quantity']), 2) ?> <?= htmlspecialchars($mv['unit']) ?>
                            </td>
                            <td class="fw-bold text-dark"><?= number_format($mv['balance_after'], 2) ?> <?= htmlspecialchars($mv['unit']) ?></td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($mv['reference_type'] ?? '-') ?> <?= $mv['reference_id'] ? '#' . $mv['reference_id'] : '' ?></div>
                                <small class="text-muted"><?= htmlspecialchars($mv['notes'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($mv['actor_name'] ?? 'Sistem') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
