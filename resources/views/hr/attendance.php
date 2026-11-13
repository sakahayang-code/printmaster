<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check me-2 text-primary"></i>Absensi & Kehadiran Karyawan</h4>
        <p class="text-muted small mb-0">Pencatatan jam masuk, jam pulang, keterlambatan, izin, sakit, dan alpha.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkInModal">
            <i class="bi bi-clock me-1"></i> Catat Absensi Masuk
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/attendance" class="row g-2 align-items-center">
            <div class="col-md-9">
                <label class="form-label small text-muted mb-1">Tanggal Absensi</label>
                <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="/attendance?date=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-secondary w-100">Hari Ini</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Cabang</th>
                    <th class="text-center">Jam Masuk</th>
                    <th class="text-center">Jam Pulang</th>
                    <th class="text-center">Status</th>
                    <th>Keterangan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendances)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada data absensi untuk tanggal <?= FormatHelper::date($date) ?>.</td>
                </tr>
                <?php else: foreach ($attendances as $a): ?>
                <tr>
                    <td><strong class="font-monospace text-primary"><?= htmlspecialchars($a['employee_code']) ?></strong></td>
                    <td><div class="fw-bold"><?= htmlspecialchars($a['employee_name']) ?></div></td>
                    <td><span class="small text-muted"><?= htmlspecialchars($a['position']) ?></span></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($a['branch_name']) ?></span></td>
                    <td class="text-center fw-semibold text-primary font-monospace">
                        <?= $a['check_in'] ? htmlspecialchars($a['check_in']) : '-' ?>
                    </td>
                    <td class="text-center fw-semibold text-secondary font-monospace">
                        <?= $a['check_out'] ? htmlspecialchars($a['check_out']) : '-' ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $badges = [
                            'present'    => '<span class="badge bg-success">Hadir Tepat Waktu</span>',
                            'late'       => '<span class="badge bg-warning text-dark">Terlambat</span>',
                            'permission' => '<span class="badge bg-info text-dark">Izin</span>',
                            'sick'       => '<span class="badge bg-secondary">Sakit</span>',
                            'leave'      => '<span class="badge bg-primary">Cuti</span>',
                            'absent'     => '<span class="badge bg-danger">Alpha / Mangkir</span>'
                        ];
                        echo $badges[$a['status']] ?? htmlspecialchars($a['status']);
                        ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($a['notes'] ?? '-') ?></td>
                    <td class="text-center">
                        <?php if (empty($a['check_out'])): ?>
                        <form method="POST" action="/attendance/check-out" class="d-inline">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="attendance_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Catat Pulang Sekarang">
                                <i class="bi bi-box-arrow-right me-1"></i> Pulang
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-success small"><i class="bi bi-check-all"></i> Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Catat Masuk -->
<div class="modal fade" id="checkInModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/attendance/check-in" class="modal-content">
            <?= CSRF::field() ?>
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock me-2 text-primary"></i>Catat Kehadiran Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Karyawan</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['position']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jam Masuk</label>
                        <input type="time" name="time" class="form-control" value="<?= date('H:i') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status Kehadiran</label>
                        <select name="status" class="form-select">
                            <option value="present">Hadir (Tepat Waktu)</option>
                            <option value="late">Terlambat</option>
                            <option value="permission">Izin</option>
                            <option value="sick">Sakit (Ada Surat Dokter)</option>
                            <option value="leave">Cuti Resmi</option>
                            <option value="absent">Alpha / Mangkir</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan / Alasan (Opsional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan izin/sakit..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Absensi</button>
            </div>
        </form>
    </div>
</div>
