<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-truck me-2 text-primary"></i>Pengiriman, Kurir & Surat Jalan</h4>
        <p class="text-muted small mb-0">Manajemen pengiriman hasil cetak ke pelanggan, penugasan kurir internal/ekspedisi, dan cetak Surat Jalan resmi.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDeliveryModal">
            <i class="bi bi-box-seam me-1"></i> Buat Surat Jalan Baru
        </button>
    </div>
</div>

<!-- Tabs Status -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a href="/delivery" class="nav-link <?= empty($currentStatus) ? 'active' : '' ?>">Semua Pengiriman</a>
    </li>
    <li class="nav-item">
        <a href="/delivery?status=in_transit" class="nav-link <?= $currentStatus === 'in_transit' ? 'active' : '' ?>">
            <i class="bi bi-truck me-1"></i> Sedang Dikirim (In Transit)
        </a>
    </li>
    <li class="nav-item">
        <a href="/delivery?status=delivered" class="nav-link <?= $currentStatus === 'delivered' ? 'active' : '' ?>">
            <i class="bi bi-check-circle me-1"></i> Sudah Diterima (Delivered)
        </a>
    </li>
    <li class="nav-item">
        <a href="/delivery?status=failed" class="nav-link <?= $currentStatus === 'failed' ? 'active' : '' ?>">
            <i class="bi bi-x-circle me-1"></i> Gagal / Retur
        </a>
    </li>
</ul>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Surat Jalan</th>
                    <th>No. Pesanan & Pelanggan</th>
                    <th>Cabang</th>
                    <th>Kurir / Ekspedisi</th>
                    <th>Penerima & Alamat</th>
                    <th>Waktu Pengiriman</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deliveries)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Belum ada pengiriman dalam status ini.</td>
                </tr>
                <?php else: foreach ($deliveries as $d): ?>
                <tr>
                    <td>
                        <strong class="text-primary font-monospace"><?= htmlspecialchars($d['delivery_number']) ?></strong>
                        <?php if ($d['tracking_number']): ?>
                        <div class="small text-muted">Resi: <?= htmlspecialchars($d['tracking_number']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/orders/<?= $d['order_id'] ?>" class="fw-semibold text-decoration-none">
                            <?= htmlspecialchars($d['order_number']) ?>
                        </a>
                        <div class="small text-muted"><?= htmlspecialchars($d['customer_name']) ?></div>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['branch_name']) ?></span></td>
                    <td>
                        <div class="fw-semibold small">
                            <?= $d['courier_type'] === 'internal' ? '<i class="bi bi-person me-1 text-primary"></i> Kurir Internal' : '<i class="bi bi-box me-1 text-secondary"></i> Ekspedisi Luar' ?>
                        </div>
                        <div class="text-muted small"><?= htmlspecialchars($d['courier_name']) ?></div>
                    </td>
                    <td>
                        <div class="fw-bold small"><?= htmlspecialchars($d['recipient_name']) ?> (<?= htmlspecialchars($d['recipient_phone']) ?>)</div>
                        <div class="text-muted small text-truncate" style="max-width: 200px;"><?= htmlspecialchars($d['delivery_address']) ?></div>
                    </td>
                    <td class="small">
                        <div>Dibuat: <?= FormatHelper::datetime($d['created_at']) ?></div>
                        <?php if ($d['actual_delivery_time']): ?>
                        <div class="text-success"><i class="bi bi-check-lg me-1"></i>Tiba: <?= FormatHelper::datetime($d['actual_delivery_time']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $badges = [
                            'pending'    => '<span class="badge bg-secondary">Menunggu</span>',
                            'in_transit' => '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>In Transit</span>',
                            'delivered'  => '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Delivered</span>',
                            'failed'     => '<span class="badge bg-danger">Failed</span>'
                        ];
                        echo $badges[$d['status']] ?? htmlspecialchars($d['status']);
                        ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="/delivery/<?= $d['id'] ?>/print" target="_blank" class="btn btn-outline-secondary" title="Cetak Surat Jalan">
                                <i class="bi bi-printer"></i>
                            </a>
                            <?php if ($d['status'] === 'in_transit'): ?>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#updateDeliveryModal<?= $d['id'] ?>" title="Konfirmasi Selesai Terkirim">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <!-- Modal Konfirmasi Pengiriman -->
                <?php if ($d['status'] === 'in_transit'): ?>
                <div class="modal fade" id="updateDeliveryModal<?= $d['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="/delivery/status" class="modal-content">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="delivery_id" value="<?= $d['id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Konfirmasi Pengiriman <?= htmlspecialchars($d['delivery_number']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Pilih Status Baru</label>
                                    <select name="status" class="form-select" required>
                                        <option value="delivered">Sudah Diterima Pelanggan (Delivered)</option>
                                        <option value="failed">Pengiriman Gagal / Alamat Tidak Ditemukan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Catatan Pengantaran</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Diterima oleh Satpam / Resepsionis / Ybs..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buat Surat Jalan Baru -->
<div class="modal fade" id="newDeliveryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/delivery/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-truck me-2 text-primary"></i>Buat Surat Jalan Pengiriman Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Pesanan Siap Kirim</label>
                    <select name="order_id" id="deliveryOrderSelect" class="form-select" required>
                        <option value="">-- Pilih Pesanan Siap Kirim --</option>
                        <?php foreach ($readyOrders as $ro): ?>
                        <option value="<?= $ro['id'] ?>" 
                                data-recipient="<?= htmlspecialchars($ro['customer_name']) ?>" 
                                data-phone="<?= htmlspecialchars($ro['customer_phone']) ?>" 
                                data-address="<?= htmlspecialchars($ro['customer_address'] ?? '') ?>">
                            <?= htmlspecialchars($ro['order_number']) ?> - <?= htmlspecialchars($ro['customer_name']) ?> (<?= FormatHelper::rupiah($ro['total_amount']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jenis Kurir</label>
                        <select name="courier_type" class="form-select" required>
                            <option value="internal">Kurir Internal Toko</option>
                            <option value="third_party">Ekspedisi Pihak Ketiga (JNE/J&T/Gojek/Grab)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Driver / Kurir / Ekspedisi</label>
                        <input type="text" name="courier_name" class="form-control" placeholder="Nama driver atau nama ekspedisi" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">No. Resi / Tracking (Jika Ekspedisi Luar)</label>
                    <input type="text" name="tracking_number" class="form-control" placeholder="Resi / Link pelacakan...">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Penerima</label>
                        <input type="text" name="recipient_name" id="recName" class="form-control" placeholder="Nama penerima di lokasi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Telepon Penerima</label>
                        <input type="text" name="recipient_phone" id="recPhone" class="form-control" placeholder="08..." required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap Pengiriman</label>
                    <textarea name="delivery_address" id="recAddress" class="form-control" rows="2" placeholder="Alamat lengkap penerima..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan Tambahan untuk Kurir</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Titip satpam / hubungi sebelum antar..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Terbitkan Surat Jalan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderSelect = document.getElementById('deliveryOrderSelect');
    const recName = document.getElementById('recName');
    const recPhone = document.getElementById('recPhone');
    const recAddress = document.getElementById('recAddress');

    orderSelect.addEventListener('change', function() {
        const opt = orderSelect.options[orderSelect.selectedIndex];
        if (opt && opt.value) {
            recName.value = opt.getAttribute('data-recipient') || '';
            recPhone.value = opt.getAttribute('data-phone') || '';
            recAddress.value = opt.getAttribute('data-address') || '';
        }
    });
});
</script>
