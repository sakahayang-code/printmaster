<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Quality Control & Cetak Ulang (QC & Reprint)</h3>
        <p class="text-muted small mb-0">Inspeksi mutu hasil cetak sebelum diserahkan ke pelanggan & pengelolaan pekerjaan ulang (reprint) tertaut.</p>
    </div>
</div>

<ul class="nav nav-pills mb-3" id="qcTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="tab-ready-btn" data-bs-toggle="pill" data-bs-target="#tab-ready">
            <i class="bi bi-clock-history me-1"></i> Siap Diinspeksi (<?= count($readyJobs) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-history-btn" data-bs-toggle="pill" data-bs-target="#tab-history">
            <i class="bi bi-shield-check me-1"></i> Riwayat Inspeksi QC (<?= count($qcLogs) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-reprints-btn" data-bs-toggle="pill" data-bs-target="#tab-reprints">
            <i class="bi bi-arrow-repeat me-1"></i> Log Cetak Ulang / Reprint (<?= count($reprints) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: SIAP DIINSPEKSI -->
    <div class="tab-pane fade show active" id="tab-ready">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Job & Order</th>
                                <th>Item Produk</th>
                                <th>Spesifikasi & Finishing</th>
                                <th>Mesin & Operator</th>
                                <th>Status Produksi</th>
                                <th class="text-end">Tindakan QC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($readyJobs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-check fs-2 d-block mb-1 text-success"></i>
                                    Tidak ada pekerjaan yang menunggu inspeksi QC saat ini.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($readyJobs as $rj): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary"><?= htmlspecialchars($rj['job_number']) ?></div>
                                        <small class="text-muted"><a href="/orders/<?= $rj['order_id'] ?>"><?= htmlspecialchars($rj['order_number']) ?></a></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($rj['item_name']) ?></div>
                                        <small class="badge bg-secondary"><?= $rj['item_qty'] ?> <?= $rj['item_unit'] ?></small>
                                    </td>
                                    <td>
                                        <div><?= ($rj['width_cm'] > 0) ? "{$rj['width_cm']}x{$rj['height_cm']} cm" : '-' ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($rj['finishing_details'] ?? 'Standar') ?></small>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-cpu me-1"></i><?= htmlspecialchars($rj['machine_name'] ?? '-') ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($rj['operator_name'] ?? '-') ?></small>
                                    </td>
                                    <td><span class="badge bg-success">Selesai Cetak</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary fw-semibold" onclick="openQCModal(<?= $rj['id'] ?>, '<?= htmlspecialchars($rj['job_number']) ?>', '<?= htmlspecialchars($rj['item_name']) ?>')">
                                            <i class="bi bi-shield-check me-1"></i> Inspeksi QC
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: RIWAYAT INSPEKSI -->
    <div class="tab-pane fade" id="tab-history">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>No. Job</th>
                                <th>Item Produk</th>
                                <th>Petugas QC</th>
                                <th>Status Hasil</th>
                                <th>Catatan Hasil Inspeksi</th>
                                <th>Tanggal Inspeksi</th>
                                <th class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($qcLogs as $log): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($log['job_number']) ?></td>
                                <td><?= htmlspecialchars($log['item_name']) ?></td>
                                <td><?= htmlspecialchars($log['inspector_name']) ?></td>
                                <td>
                                    <?php if ($log['status'] === 'passed'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lulus (Passed)</span>
                                    <?php elseif ($log['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak (Rejected)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-wrench me-1"></i>Perlu Perbaikan</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['defect_notes'] ?: 'Memenuhi seluruh standar kualitas') ?></td>
                                <td><?= FormatHelper::tanggal($log['check_date']) ?></td>
                                <td class="text-end">
                                    <?php if ($log['status'] === 'rejected'): ?>
                                    <button class="btn btn-sm btn-outline-danger" onclick="openReprintModal(<?= $log['production_job_id'] ?>, '<?= htmlspecialchars($log['job_number']) ?>')">
                                        <i class="bi bi-arrow-repeat me-1"></i> Cetak Ulang
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: LOG REPRINTS -->
    <div class="tab-pane fade" id="tab-reprints">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Job Asal</th>
                                <th>Kategori Cacat</th>
                                <th>Penanggung Jawab</th>
                                <th>Alasan Cetak Ulang</th>
                                <th>Dampak Biaya HPP</th>
                                <th>Disetujui Oleh</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reprints)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada catatan cetak ulang (reprint).</td></tr>
                            <?php else: ?>
                                <?php foreach ($reprints as $rp): ?>
                                <tr>
                                    <td class="fw-bold text-danger"><?= htmlspecialchars($rp['original_job_number']) ?></td>
                                    <td><span class="badge bg-dark"><?= htmlspecialchars($rp['defect_category']) ?></span></td>
                                    <td><?= htmlspecialchars($rp['responsible_role']) ?></td>
                                    <td><?= htmlspecialchars($rp['reason']) ?></td>
                                    <td class="fw-bold text-danger"><?= FormatHelper::rupiah($rp['cost_impact']) ?></td>
                                    <td><?= htmlspecialchars($rp['approver_name'] ?? 'Supervisor') ?></td>
                                    <td><?= FormatHelper::tanggal($rp['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORM INSPEKSI QC -->
<div class="modal fade" id="modalInspectQC" tabindex="-1">
    <div class="modal-dialog">
        <form action="/qc/inspect" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <input type="hidden" name="production_job_id" id="qcJobId">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Lembar Pemeriksaan Mutu (QC)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="fw-bold text-dark" id="qcJobTitle">Job: -</div>
                    <small class="text-muted" id="qcItemTitle">Item: -</small>
                </div>

                <div class="fw-bold small text-uppercase mb-2 text-secondary">Checklist Parameter Mutu:</div>
                <div class="p-3 border rounded mb-3 bg-white">
                    <div class="form-check py-1">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="color_match" id="c1" checked>
                        <label class="form-check-label small" for="c1">Kesesuaian warna & ketajaman gambar dengan proof</label>
                    </div>
                    <div class="form-check py-1">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="size_accuracy" id="c2" checked>
                        <label class="form-check-label small" for="c2">Akurasi ukuran fisik cetakan (panjang x lebar presisi)</label>
                    </div>
                    <div class="form-check py-1">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="cutting_neat" id="c3" checked>
                        <label class="form-check-label small" for="c3">Kerapian pemotongan sudut / cutting kiss-cut</label>
                    </div>
                    <div class="form-check py-1">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="finishing_quality" id="c4" checked>
                        <label class="form-check-label small" for="c4">Kekuatan finishing (mata ayam kencang / laminasi tanpa gelembung)</label>
                    </div>
                    <div class="form-check py-1">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="cleanliness" id="c5" checked>
                        <label class="form-check-label small" for="c5">Kebersihan fisik (bebas noda tinta tumpah / garis printhead)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Keputusan Hasil Inspeksi</label>
                    <select name="status" class="form-select form-select-lg" required>
                        <option value="passed" selected class="text-success fw-bold">✓ LULUS (Passed) - Siap Diserahkan</option>
                        <option value="needs_correction" class="text-warning fw-bold">⚠ Perlu Perbaikan Kecil (Rework Finishing)</option>
                        <option value="rejected" class="text-danger fw-bold">✗ REJECT (Tolak & Butuh Cetak Ulang)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Temuan / Alasan Cacat</label>
                    <textarea name="defect_notes" class="form-control" rows="2" placeholder="Catatan khusus kondisi hasil cetak..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Hasil QC</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CETAK ULANG (REPRINT) -->
<div class="modal fade" id="modalReprint" tabindex="-1">
    <div class="modal-dialog">
        <form action="/qc/reprint" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <input type="hidden" name="original_job_id" id="reprintOrigJobId">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Instruksi Cetak Ulang (Reprint)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Cetak ulang akan otomatis membuat job produksi baru yang tertaut ke job asal dengan prioritas Urgent tanpa mengubah order asli pelanggan.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kategori Cacat</label>
                    <select name="defect_category" class="form-select">
                        <option value="Warna Belang / Garis Printhead">Warna Belang / Garis Printhead</option>
                        <option value="Salah Ukuran / Salah Potong">Salah Ukuran / Salah Potong</option>
                        <option value="Laminasi Mengelupas / Gelembung">Laminasi Mengelupas / Gelembung</option>
                        <option value="Bahan Rusak / Sobek">Bahan Rusak / Sobek</option>
                        <option value="Salah File Desain / Typo">Salah File Desain / Typo</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pihak Penanggung Jawab Biaya</label>
                    <select name="responsible_role" class="form-select">
                        <option value="Operator Cetak">Operator Cetak</option>
                        <option value="Operator Finishing">Operator Finishing</option>
                        <option value="Desainer">Desainer Prepress</option>
                        <option value="Kerusakan Mesin">Kerusakan Mesin</option>
                        <option value="Toleransi Perusahaan">Toleransi Perusahaan (Waste Allowance)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Estimasi Dampak Biaya Bahan (Rp)</label>
                    <input type="number" name="cost_impact" class="form-control" value="25000" min="0" step="1000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Lengkap Cetak Ulang</label>
                    <textarea name="reason" class="form-control" rows="2" required placeholder="Jelaskan detail penyebab kegagalan cetak..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger fw-bold">Buat Job Cetak Ulang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQCModal(id, jobNum, item) {
    document.getElementById('qcJobId').value = id;
    document.getElementById('qcJobTitle').innerText = 'Job Produksi: ' + jobNum;
    document.getElementById('qcItemTitle').innerText = 'Item: ' + item;
    new bootstrap.Modal(document.getElementById('modalInspectQC')).show();
}

function openReprintModal(origId, jobNum) {
    document.getElementById('reprintOrigJobId').value = origId;
    new bootstrap.Modal(document.getElementById('modalReprint')).show();
}
</script>
