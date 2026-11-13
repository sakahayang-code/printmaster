<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-whatsapp me-2 text-success"></i>Integrasi Notifikasi WhatsApp Gateway</h4>
        <p class="text-muted small mb-0">Simulator dan pengirim pesan notifikasi otomatis untuk order konfirmasi, pembayaran diterima, dan pesanan selesai/siap diambil.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#testSendModal">
            <i class="bi bi-send-fill me-1"></i> Uji Coba Kirim WA
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Template WhatsApp List -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-square-dots me-2 text-primary"></i>Template Pesan Otomatis</h6>
            </div>
            <div class="card-body p-3">
                <?php foreach ($templates as $tpl): ?>
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary small"><?= htmlspecialchars($tpl['title']) ?></strong>
                        <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($tpl['event_code']) ?></span>
                    </div>
                    <form method="POST" action="/whatsapp/template">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="template_id" value="<?= $tpl['id'] ?>">
                        <textarea name="message_template" class="form-control form-control-sm font-monospace mb-2" rows="3"><?= htmlspecialchars($tpl['message_template']) ?></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Variabel: {{customer_name}}, {{order_number}}</small>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Live Logs -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-success"></i>Log Pesan WhatsApp Terkirim</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Tujuan</th>
                            <th>Subjek / Pesan</th>
                            <th class="text-center">Status</th>
                            <th>Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat pesan dikirim.</td>
                        </tr>
                        <?php else: foreach ($logs as $l): ?>
                        <tr>
                            <td>
                                <strong class="text-success font-monospace"><i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($l['recipient']) ?></strong>
                                <?php if ($l['branch_name']): ?>
                                <div class="small text-muted"><?= htmlspecialchars($l['branch_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($l['subject'] ?? 'Notifikasi') ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 250px;">
                                    <?= htmlspecialchars($l['message_content']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($l['status'] === 'sent'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2-all me-1"></i>Sent</span>
                                <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><?= htmlspecialchars($l['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= FormatHelper::datetime($l['sent_at'] ?? $l['created_at']) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Test Send WA -->
<div class="modal fade" id="testSendModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/whatsapp/send-test" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-whatsapp me-2 text-success"></i>Simulasi Kirim Notifikasi WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor WhatsApp Tujuan</label>
                    <input type="text" name="phone" class="form-control" placeholder="08123456789 atau 628123456789" required>
                    <small class="text-muted">Nomor ponsel penerima untuk simulator.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Jenis Notifikasi / Template</label>
                    <select name="event_code" id="eventSelect" class="form-select" required>
                        <option value="order_confirmed">Konfirmasi Pesanan Baru (order_confirmed)</option>
                        <option value="payment_received">Bukti Pembayaran Diterima (payment_received)</option>
                        <option value="order_ready">Pesanan Siap Diambil / Selesai (order_ready)</option>
                        <option value="custom">Pesan Bebas (Custom Text)</option>
                    </select>
                </div>
                <div class="mb-3" id="customMsgGroup" style="display: none;">
                    <label class="form-label fw-semibold">Isi Pesan Kustom</label>
                    <textarea name="custom_message" class="form-control" rows="3" placeholder="Tulis pesan Anda di sini..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i> Kirim Pesan Uji Coba</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const evSel = document.getElementById('eventSelect');
    const customGroup = document.getElementById('customMsgGroup');
    evSel.addEventListener('change', function() {
        customGroup.style.display = evSel.value === 'custom' ? 'block' : 'none';
    });
});
</script>
