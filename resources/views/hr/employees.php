<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Karyawan & Manajemen SDM</h4>
        <p class="text-muted small mb-0">Pengelolaan data personal karyawan percetakan, jabatan, gaji pokok, tunjangan, dan status kepegawaian.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-person-plus me-1"></i> Tambah Karyawan Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/employees" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama, kode karyawan, atau jabatan..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Cari Karyawan</button>
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
                    <th>Cabang</th>
                    <th>Jabatan & Dept</th>
                    <th>Kontak (No HP)</th>
                    <th class="text-end">Gaji Pokok</th>
                    <th class="text-end">Tunjangan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada data karyawan.</td>
                </tr>
                <?php else: foreach ($employees as $emp): ?>
                <tr>
                    <td><strong class="font-monospace text-primary"><?= htmlspecialchars($emp['employee_code']) ?></strong></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($emp['name']) ?></div>
                        <small class="text-muted">Masuk: <?= FormatHelper::date($emp['join_date']) ?></small>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($emp['branch_name']) ?></span></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($emp['position']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($emp['department']) ?></div>
                    </td>
                    <td>
                        <div><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($emp['phone']) ?></div>
                        <?php if ($emp['email']): ?>
                        <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($emp['email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-semibold"><?= FormatHelper::rupiah($emp['basic_salary']) ?></td>
                    <td class="text-end text-muted small"><?= FormatHelper::rupiah($emp['allowance_fixed']) ?></td>
                    <td class="text-center">
                        <?php if ($emp['status'] === 'active'): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php elseif ($emp['status'] === 'probation'): ?>
                        <span class="badge bg-warning text-dark">Probation</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Resigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editEmpModal<?= $emp['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>

                <!-- Modal Edit Karyawan -->
                <div class="modal fade" id="editEmpModal<?= $emp['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="/employees/update" class="modal-content">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Karyawan: <?= htmlspecialchars($emp['name']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($emp['name']) ?>" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Jabatan</label>
                                        <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($emp['position']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Departemen</label>
                                        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($emp['department']) ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">No. WhatsApp / HP</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($emp['phone']) ?>" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Gaji Pokok</label>
                                        <input type="number" name="basic_salary" class="form-control" value="<?= $emp['basic_salary'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tunjangan Tetap</label>
                                        <input type="number" name="allowance_fixed" class="form-control" value="<?= $emp['allowance_fixed'] ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status Kepegawaian</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?= $emp['status'] === 'active' ? 'selected' : '' ?>>Aktif (Tetap/Kontrak)</option>
                                        <option value="probation" <?= $emp['status'] === 'probation' ? 'selected' : '' ?>>Probation (Masa Percobaan)</option>
                                        <option value="resigned" <?= $emp['status'] === 'resigned' ? 'selected' : '' ?>>Resigned (Berhenti)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/employees/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Karyawan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cabang Penempatan</label>
                    <select name="branch_id" class="form-select" required>
                        <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap Karyawan</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama lengkap..." required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan</label>
                        <input type="text" name="position" class="form-control" placeholder="Kasir, Operator, Desainer..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Departemen</label>
                        <select name="department" class="form-select">
                            <option value="Operasional Produksi">Operasional Produksi</option>
                            <option value="Kasir & Front Office">Kasir & Front Office</option>
                            <option value="Desain & Kreatif">Desain & Kreatif</option>
                            <option value="Gudang & Logistik">Gudang & Logistik</option>
                            <option value="Manajemen & Keuangan">Manajemen & Keuangan</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" placeholder="08123456789" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" placeholder="email@perusahaan.com">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Gaji Pokok (Rp)</label>
                        <input type="number" name="basic_salary" class="form-control" placeholder="4000000" min="0" step="50000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tunjangan Tetap (Rp)</label>
                        <input type="number" name="allowance_fixed" class="form-control" placeholder="500000" min="0" step="50000" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Mulai Bekerja</label>
                    <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Daftarkan Karyawan</button>
            </div>
        </form>
    </div>
</div>
