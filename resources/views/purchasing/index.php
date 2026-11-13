<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Pembelian Bahan & PO Supplier</h3>
        <p class="text-muted small mb-0">Kelola pesanan pembelian (Purchase Order) ke pemasok, kontrol penerimaan barang fisik, dan pencatatan hutang dagang.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/suppliers" class="btn btn-outline-secondary btn-sm"><i class="bi bi-truck-front me-1"></i> Data Pemasok</a>
        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePO">
            <i class="bi bi-plus-circle-fill me-1"></i> Buat PO Pembelian Baru
        </button>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>No. PO & Tanggal</th>
                        <th>Supplier</th>
                        <th>Cabang & Gudang</th>
                        <th>Total Pembelian</th>
                        <th>Status Barang</th>
                        <th>Status Bayar</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Aksi Penerimaan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada purchase order yang dibuat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $po): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($po['po_number']) ?></div>
                                <small class="text-muted"><?= FormatHelper::tanggal($po['po_date'], false) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($po['supplier_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($po['supplier_phone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($po['branch_name']) ?></td>
                            <td class="fw-bold text-dark"><?= FormatHelper::rupiah($po['total_amount']) ?></td>
                            <td>
                                <?php if ($po['status'] === 'received'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Diterima Gudang</span>
                                <?php elseif ($po['status'] === 'approved'): ?>
                                    <span class="badge bg-primary">PO Disetujui (Menunggu Pengiriman)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($po['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= FormatHelper::badgeStatusPayment($po['payment_status']) ?></td>
                            <td><?= FormatHelper::tanggal($po['due_date'], false) ?></td>
                            <td class="text-end">
                                <?php if ($po['status'] !== 'received'): ?>
                                <button class="btn btn-sm btn-success fw-bold" onclick="openReceiveModal(<?= $po['id'] ?>, '<?= htmlspecialchars($po['po_number']) ?>')">
                                    <i class="bi bi-box-seam me-1"></i> Terima Barang
                                </button>
                                <?php else: ?>
                                <span class="badge bg-light text-secondary border">Selesai</span>
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

<!-- MODAL BUAT PO -->
<div class="modal fade" id="modalCreatePO" tabindex="-1">
    <div class="modal-dialog">
        <form action="/purchasing/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Buat Purchase Order (PO) Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Supplier / Pemasok</label>
                    <select name="supplier_id" class="form-select" required>
                        <?php foreach ($suppliers as $sp): ?>
                        <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['name']) ?> (Tempo: <?= $sp['payment_terms_days'] ?> hari)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Gudang Penerima</label>
                    <select name="warehouse_id" class="form-select" required>
                        <?php foreach ($warehouses as $wh): ?>
                        <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tanggal PO</label>
                        <input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Jatuh Tempo Pembayaran</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Bahan Baku yang Dipesan</label>
                    <select name="material_id" id="poMaterialSelect" class="form-select" onchange="updatePOMatPrice()" required>
                        <?php foreach ($materials as $m): ?>
                        <option value="<?= $m['id'] ?>" data-cost="<?= $m['avg_cost'] ?>" data-unit="<?= htmlspecialchars($m['unit']) ?>">
                            <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['unit']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Jumlah (Kuantitas)</label>
                        <input type="number" name="quantity" id="poQty" class="form-control" value="10" min="1" step="0.5" oninput="calcPOTotal()" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Harga Beli Satuan (Rp)</label>
                        <input type="number" name="unit_price" id="poPrice" class="form-control" value="10000" min="0" step="500" oninput="calcPOTotal()" required>
                    </div>
                </div>

                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Total Pembelian:</span>
                    <span id="poTotalTxt" class="fs-5 fw-bold text-primary">Rp 100.000</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small text-secondary">Catatan PO</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Nomor penawaran supplier / armada kirim...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Terbitkan PO</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TERIMA BARANG PO -->
<div class="modal fade" id="modalReceivePO" tabindex="-1">
    <div class="modal-dialog">
        <form id="formReceivePO" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Penerimaan Barang Fisik PO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Penerimaan barang akan otomatis menambahkan stok fisik ke kartu stok gudang dan mencatat hutang supplier pada jurnal akuntansi.</p>
                <div class="fw-bold mb-3 text-dark fs-6" id="txtReceivePONumber">PO: -</div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Surat Jalan dari Vendor / Supplier</label>
                    <input type="text" name="delivery_note_number" class="form-control" placeholder="Contoh: SJ-VENDOR-88992" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold">Konfirmasi Barang Diterima</button>
            </div>
        </form>
    </div>
</div>

<script>
function updatePOMatPrice() {
    const sel = document.getElementById('poMaterialSelect');
    const opt = sel.options[sel.selectedIndex];
    if (opt) {
        document.getElementById('poPrice').value = parseFloat(opt.getAttribute('data-cost')) || 0;
        calcPOTotal();
    }
}

function calcPOTotal() {
    const q = parseFloat(document.getElementById('poQty').value) || 0;
    const p = parseFloat(document.getElementById('poPrice').value) || 0;
    const tot = q * p;
    document.getElementById('poTotalTxt').innerText = 'Rp ' + Math.round(tot).toLocaleString('id-ID');
}

function openReceiveModal(id, num) {
    document.getElementById('formReceivePO').action = '/purchasing/' + id + '/receive';
    document.getElementById('txtReceivePONumber').innerText = 'No. Purchase Order: ' + num;
    new bootstrap.Modal(document.getElementById('modalReceivePO')).show();
}

document.addEventListener('DOMContentLoaded', updatePOMatPrice);
</script>
