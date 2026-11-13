<?php use App\Core\CSRF; ?>
<form action="/login" method="POST">
    <?= CSRF::field() ?>
    <div class="mb-3">
        <label for="username" class="form-label fw-semibold text-secondary small">USERNAME ATAU EMAIL</label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus value="admin">
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between">
            <label for="password" class="form-label fw-semibold text-secondary small">PASSWORD</label>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required value="password123">
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
    </button>
</form>

<div class="mt-4 pt-3 border-top">
    <div class="text-muted small fw-bold mb-2">AKUN PENGUJIAN CEPAT (Password: password123):</div>
    <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('admin')">Super Admin</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('owner')">Owner</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('kacab.jkt')">Kacab Jkt</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('kacab.sby')">Kacab Sby</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('kasir.jkt')">Kasir Jkt</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('desainer1')">Desainer</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('prodman')">Prod Man</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('operator1')">Operator</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('qc1')">QC</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('gudang1')">Gudang</button>
        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs" onclick="setLogin('akuntan1')">Akuntan</button>
    </div>
</div>

<script>
function setLogin(u) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = 'password123';
}
</script>
