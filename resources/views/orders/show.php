<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="/orders" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Order</a>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($order['order_number']) ?></h3>
            <span class="badge bg-secondary"><?= htmlspecialchars($order['branch_name']) ?></span>
        </div>
        <div class="text-muted small mt-1">
            Invoice: <strong><?= htmlspecialchars($order['invoice_number']) ?></strong> • Dibuat: <?= FormatHelper::tanggal($order['created_at']) ?>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <!-- Cetak Dropdown -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="/orders/<?= $order['id'] ?>/invoice" target="_blank"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Cetak Invoice A4</a></li>
                <li><a class="dropdown-item" href="/orders/<?= $order['id'] ?>/receipt" target="_blank"><i class="bi bi-receipt me-2 text-primary"></i>Cetak Struk Kasir (Thermal)</a></li>
                <li><a class="dropdown-item" href="/orders/<?= $order['id'] ?>/work-order" target="_blank"><i class="bi bi-tools me-2 text-warning"></i>Cetak SPK Kerja Produksi</a></li>
            </ul>
        </div>

        <?php if ($order['remaining_amount'] > 0 && Auth::hasPermission('payments.receive')): ?>
        <button class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPayment">
            <i class="bi bi-cash-stack me-1"></i> Terima Pembayaran
        </button>
        <?php endif; ?>

        <?php if (Auth::hasPermission('orders.edit')): ?>
        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpdateStatus">
            <i class="bi bi-arrow-repeat me-1"></i> Update Status Order
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- KARTU RINGKASAN STATUS & PELANGGAN -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Status Pesanan</span>
            <div class="mt-2"><?= FormatHelper::badgeStatusOrder($order['status']) ?></div>
            <div class="text-muted small mt-2">Tenggat: <span class="fw-semibold text-dark"><?= FormatHelper::tanggal($order['deadline_date']) ?></span></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Status Pembayaran</span>
            <div class="mt-2"><?= FormatHelper::badgeStatusPayment($order['payment_status']) ?></div>
            <div class="small mt-2">
                <span class="text-success fw-bold"><?= FormatHelper::rupiah($order['paid_amount']) ?></span> 
                <?php if ($order['remaining_amount'] > 0): ?>
                / <span class="text-danger fw-bold">Sisa: <?= FormatHelper::rupiah($order['remaining_amount']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Informasi Pelanggan</span>
            <div class="fw-bold text-dark mt-1"><?= htmlspecialchars($order['customer_name']) ?></div>
            <div class="text-muted small"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($order['customer_phone']) ?></div>
            <span class="badge bg-light text-secondary border mt-1"><?= strtoupper($order['customer_tier']) ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 h-100">
            <span class="text-muted small text-uppercase fw-semibold">Metode Serah Terima</span>
            <div class="fw-bold text-dark mt-1"><?= strtoupper(str_replace('_', ' ', $order['pickup_delivery_type'])) ?></div>
            <?php if (!empty($order['shipping_address'])): ?>
            <div class="text-muted small text-truncate mt-1" title="<?= htmlspecialchars($order['shipping_address']) ?>"><?= htmlspecialchars($order['shipping_address']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- NAV TABS -->
<div class="card card-custom">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs card-header-tabs m-0" id="orderDetailTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-3 px-4 fw-semibold text-dark" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-pane" type="button" role="tab">
                    <i class="bi bi-box-seam me-2 text-primary"></i>Item Pesanan (<?= count($items) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold text-dark" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane" type="button" role="tab">
                    <i class="bi bi-credit-card me-2 text-success"></i>Pembayaran (<?= count($payments) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold text-dark" id="jobs-tab" data-bs-toggle="tab" data-bs-target="#jobs-pane" type="button" role="tab">
                    <i class="bi bi-gear-wide me-2 text-warning"></i>Job Produksi (<?= count($jobs) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold text-dark" id="files-tab" data-bs-toggle="tab" data-bs-target="#files-pane" type="button" role="tab">
                    <i class="bi bi-paperclip me-2 text-info"></i>File Desain (<?= count($files) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold text-dark" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
                    <i class="bi bi-clock-history me-2 text-secondary"></i>Riwayat Status (<?= count($history) ?>)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="orderTabContent">
            <!-- TAB 1: ITEM PESANAN -->
            <div class="tab-pane fade show active" id="items-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No</th>
                                <th>Produk & Rincian Spesifikasi</th>
                                <th>Ukuran</th>
                                <th>Qty</th>
                                <th>Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($it['item_name']) ?></div>
                                    <?php if (!empty($it['description'])): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($it['description']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($it['finishing_details'])): ?>
                                    <div class="mt-1"><span class="badge bg-light text-secondary border">Finishing: <?= htmlspecialchars($it['finishing_details']) ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= ($it['width_cm'] > 0) ? "{$it['width_cm']} x {$it['height_cm']} cm ({$it['area_sqm']} m²)" : '-' ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></span></td>
                                <td><?= FormatHelper::rupiah($it['unit_price']) ?></td>
                                <td class="text-end fw-bold text-dark"><?= FormatHelper::rupiah($it['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">Subtotal Item:</th>
                                <th class="text-end"><?= FormatHelper::rupiah($order['subtotal']) ?></th>
                            </tr>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <tr>
                                <th colspan="5" class="text-end text-danger">Diskon <?= $order['voucher_code'] ? "({$order['voucher_code']})" : '' ?>:</th>
                                <th class="text-end text-danger">- <?= FormatHelper::rupiah($order['discount_amount']) ?></th>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th colspan="5" class="text-end fs-5">TOTAL TAGIHAN:</th>
                                <th class="text-end fs-5 text-primary fw-bold"><?= FormatHelper::rupiah($order['total_amount']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- TAB 2: RIWAYAT PEMBAYARAN -->
            <div class="tab-pane fade" id="payments-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Pembayaran</th>
                                <th>Tanggal & Waktu</th>
                                <th>Metode</th>
                                <th>Nominal</th>
                                <th>Nomor Referensi</th>
                                <th>Diverifikasi Oleh</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada pembayaran yang dicatat.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($pay['payment_number']) ?></td>
                                    <td><?= FormatHelper::tanggal($pay['payment_date']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= strtoupper($pay['payment_method']) ?></span></td>
                                    <td class="fw-bold text-success"><?= FormatHelper::rupiah($pay['amount']) ?></td>
                                    <td><?= htmlspecialchars($pay['reference_number'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($pay['verifier_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i><?= ucfirst($pay['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: STATUS JOB PRODUKSI -->
            <div class="tab-pane fade" id="jobs-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Job Produksi</th>
                                <th>Mesin</th>
                                <th>Operator</th>
                                <th>Cabang Pelaksana</th>
                                <th>Prioritas</th>
                                <th>Status Job</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($job['job_number']) ?></td>
                                <td><?= htmlspecialchars($job['machine_name'] ?? 'Belum dialokasikan') ?></td>
                                <td><?= htmlspecialchars($job['operator_name'] ?? 'Belum ditugaskan') ?></td>
                                <td><?= htmlspecialchars($job['target_branch_name'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= strtoupper($job['priority']) ?></span></td>
                                <td><?= FormatHelper::badgeStatusJob($job['status']) ?></td>
                                <td>
                                    <a href="/production" class="btn btn-sm btn-outline-primary"><i class="bi bi-kanban me-1"></i>Papan Produksi</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: FILE DESAIN -->
            <div class="tab-pane fade" id="files-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Lampiran File Desain Pelanggan</h6>
                    <a href="/designs" class="btn btn-sm btn-primary"><i class="bi bi-upload me-1"></i>Kelola di Modul Desain</a>
                </div>
                <?php if (empty($files)): ?>
                <div class="text-center py-4 text-muted border rounded bg-light">
                    <i class="bi bi-file-earmark-arrow-up fs-2 d-block mb-1 text-secondary"></i>
                    Belum ada file desain yang diunggah untuk order ini.
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($files as $f): ?>
                    <div class="col-md-4">
                        <div class="card p-3 border">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-image fs-2 text-primary"></i>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-truncate"><?= htmlspecialchars($f['original_filename']) ?></div>
                                    <small class="text-muted"><?= round($f['file_size'] / 1024, 1) ?> KB • <?= htmlspecialchars($f['mime_type']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- TAB 5: RIWAYAT STATUS -->
            <div class="tab-pane fade" id="history-pane" role="tabpanel">
                <ul class="timeline list-unstyled position-relative border-start ps-3 ms-3 py-2">
                    <?php foreach ($history as $h): ?>
                    <li class="mb-3 position-relative">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle p-1"><i class="bi bi-check2"></i></span>
                            <span class="fw-bold text-dark"><?= ucfirst(str_replace('_', ' ', $h['new_status'])) ?></span>
                            <span class="text-muted small">• <?= FormatHelper::tanggal($h['created_at']) ?></span>
                        </div>
                        <div class="text-muted small ms-4 mt-1">
                            <?= htmlspecialchars($h['notes'] ?? '-') ?> (Oleh: <?= htmlspecialchars($h['changer_name'] ?? 'Sistem') ?>)
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- MODAL UPDATE STATUS ORDER -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1">
    <div class="modal-dialog">
        <form action="/orders/<?= $order['id'] ?>/status" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Update Status Alur Pesanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Status Baru</label>
                    <select name="status" class="form-select" required>
                        <option value="pending_confirmation" <?= $order['status'] === 'pending_confirmation' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                        <option value="awaiting_payment" <?= $order['status'] === 'awaiting_payment' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                        <option value="awaiting_design" <?= $order['status'] === 'awaiting_design' ? 'selected' : '' ?>>Menunggu File Desain</option>
                        <option value="design_in_progress" <?= $order['status'] === 'design_in_progress' ? 'selected' : '' ?>>Desain Dikerjakan</option>
                        <option value="awaiting_design_approval" <?= $order['status'] === 'awaiting_design_approval' ? 'selected' : '' ?>>Menunggu ACC Desain</option>
                        <option value="queued" <?= $order['status'] === 'queued' ? 'selected' : '' ?>>Antrean Produksi</option>
                        <option value="in_production" <?= $order['status'] === 'in_production' ? 'selected' : '' ?>>Sedang Diproduksi</option>
                        <option value="finishing" <?= $order['status'] === 'finishing' ? 'selected' : '' ?>>Tahap Finishing</option>
                        <option value="quality_control" <?= $order['status'] === 'quality_control' ? 'selected' : '' ?>>Inspeksi QC</option>
                        <option value="ready_for_pickup" <?= $order['status'] === 'ready_for_pickup' ? 'selected' : '' ?>>Siap Diambil (Ready)</option>
                        <option value="in_delivery" <?= $order['status'] === 'in_delivery' ? 'selected' : '' ?>>Dalam Pengiriman</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Selesai & Diterima</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Perubahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Alasan perubahan atau catatan progres..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Status</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TERIMA PEMBAYARAN -->
<div class="modal fade" id="modalAddPayment" tabindex="-1">
    <div class="modal-dialog">
        <form action="/orders/<?= $order['id'] ?>/payments" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Catat Pembayaran Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Total Tagihan:</span>
                        <span class="fw-bold text-dark"><?= FormatHelper::rupiah($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>Sudah Dibayar:</span>
                        <span class="fw-bold text-success"><?= FormatHelper::rupiah($order['paid_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1 pt-1 border-top">
                        <span class="fw-bold text-dark">Sisa Tagihan:</span>
                        <span class="fw-bold text-danger"><?= FormatHelper::rupiah($order['remaining_amount']) ?></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control form-control-lg text-end fw-bold" 
                           value="<?= $order['remaining_amount'] ?>" max="<?= $order['remaining_amount'] ?>" min="1000" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Tunai (Kasir)</option>
                        <option value="bank_transfer">Transfer Bank (BCA/Mandiri)</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Referensi Transaksi</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="Contoh: TRF-BCA-998822">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Pembayaran</label>
                    <input type="text" name="notes" class="form-control" placeholder="Pelunasan pesanan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold">Konfirmasi Pembayaran</button>
            </div>
        </form>
    </div>
</div>
