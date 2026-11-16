<?php use App\Core\CSRF; ?>
<form action="/login" method="POST">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label for="username" class="form-label small fw-bold text-secondary">USERNAME ATAU EMAIL</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username / email" required autofocus value="admin">
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-bold text-secondary mb-0">PASSWORD</label>
            <span class="text-muted small" style="font-size: 0.75rem;">Default: password123</span>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock-fill"></i></span>
            <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" placeholder="Masukkan password" required value="password123">
            <button class="btn btn-outline-secondary border-start-0 bg-light" type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem ERP
    </button>
</form>

<div class="mt-4 pt-3 border-top">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small fw-bold">AKUN PENGUJIAN ROLE:</span>
        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Klik untuk pilih</span>
    </div>
    <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('admin')">Super Admin</button>
        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('owner')">Owner</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('kacab.jkt')">Kacab Jkt</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('kacab.sby')">Kacab Sby</button>
        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('kasir.jkt')">Kasir Jkt</button>
        <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('desainer1')">Desainer</button>
        <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('prodman')">Prod Man</button>
        <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('operator1')">Operator</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('qc1')">QC</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('gudang1')">Gudang</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="setLogin('akuntan1')">Akuntan</button>
    </div>
</div>

<script>
function setLogin(u) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = 'password123';
}

function togglePasswordVisibility() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
