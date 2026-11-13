<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-primary"></i>Pengguna, Jabatan & Hak Akses</h4>
        <p class="text-muted small mb-0">Manajemen akun staf percetakan, penugasan role (Super Admin, Kasir, Operator, Desainer, QC, dll) dan cabang operasional.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama & Username</th>
                    <th>Role (Peran)</th>
                    <th>Cabang Penempatan</th>
                    <th>Kontak (Email & HP)</th>
                    <th>Login Terakhir</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                                <small class="text-muted font-monospace">@<?= htmlspecialchars($u['username']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['branch_name']): ?>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($u['branch_name']) ?></span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Semua Cabang (Global)</span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <div><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($u['email']) ?></div>
                        <?php if ($u['phone']): ?>
                        <div class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($u['phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?= $u['last_login_at'] ? FormatHelper::datetime($u['last_login_at']) : 'Belum pernah' ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u['is_active']): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <form method="POST" action="/users/<?= $u['id'] ?>/reset-password" class="d-inline" onsubmit="return confirm('Reset password ke password123?')">
                                <?= CSRF::field() ?>
                                <button type="submit" class="btn btn-outline-warning" title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>
                            </form>
                            <?php if ($u['id'] !== 1): ?>
                            <form method="POST" action="/users/<?= $u['id'] ?>/toggle" class="d-inline">
                                <?= CSRF::field() ?>
                                <button type="submit" class="btn <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Ubah Status">
                                    <i class="bi <?= $u['is_active'] ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/users/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pengguna ERP Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama lengkap staf..." required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control font-monospace" placeholder="kasir_jkt" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="staf@printmaster.id" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role / Jabatan Akses</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cabang Tugas</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Semua Cabang (Akses Global)</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. WhatsApp / HP</label>
                        <input type="text" name="phone" class="form-control" placeholder="08...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password Default</label>
                        <input type="text" name="password" class="form-control" value="password123">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>
