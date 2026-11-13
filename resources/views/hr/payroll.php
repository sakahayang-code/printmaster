<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-wallet me-2 text-primary"></i>Penggajian Karyawan (Payroll)</h4>
        <p class="text-muted small mb-0">Perhitungan gaji bulanan otomatis, tunjangan, potongan absensi/keterlambatan, dan integrasi jurnal beban gaji.</p>
    </div>
    <div class="col-auto d-flex gap-2">
        <div class="card bg-primary text-white px-3 py-2 border-0 shadow-sm rounded-3">
            <small class="opacity-75">Total Netto Gaji Bulan Ini</small>
            <div class="fw-bold fs-5"><?= FormatHelper::rupiah($totalNet) ?></div>
        </div>
        <form method="POST" action="/payroll/generate">
            <?= CSRF::field() ?>
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <button type="submit" class="btn btn-success h-100 d-flex align-items-center">
                <i class="bi bi-gear-fill me-1"></i> Generate Payroll Periode Ini
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/payroll" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Bulan Gaji</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php
                    $months = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                    foreach ($months as $num => $mName):
                    ?>
                    <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $mName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Tahun</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan & Cabang</th>
                    <th class="text-end">Gaji Pokok</th>
                    <th class="text-end">Tunjangan</th>
                    <th class="text-end text-danger">Potongan</th>
                    <th class="text-end fw-bold">Gaji Bersih (THP)</th>
                    <th class="text-center">Status Bayar</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payrolls)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        Belum ada data slip payroll untuk periode bulan <?= $months[$month] ?> <?= $year ?>.<br>
                        Klik tombol <strong>Generate Payroll</strong> di atas untuk membuat data slip otomatis.
                    </td>
                </tr>
                <?php else: foreach ($payrolls as $p): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($p['employee_name']) ?></div>
                        <small class="text-muted font-monospace"><?= htmlspecialchars($p['employee_code']) ?></small>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?= htmlspecialchars($p['position']) ?></div>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['branch_name']) ?></span>
                    </td>
                    <td class="text-end"><?= FormatHelper::rupiah($p['basic_salary']) ?></td>
                    <td class="text-end text-success">+<?= FormatHelper::rupiah($p['allowances']) ?></td>
                    <td class="text-end text-danger">-<?= FormatHelper::rupiah($p['deductions']) ?></td>
                    <td class="text-end fw-bold text-primary fs-6"><?= FormatHelper::rupiah($p['net_salary']) ?></td>
                    <td class="text-center">
                        <?php if ($p['payment_status'] === 'paid'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle me-1"></i>Sudah Dibayar
                        </span>
                        <div class="small text-muted"><?= FormatHelper::datetime($p['paid_at']) ?></div>
                        <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-clock me-1"></i>Draft (Belum Bayar)
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($p['payment_status'] === 'draft'): ?>
                        <form method="POST" action="/payroll/pay" class="d-inline">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="payroll_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Konfirmasi pembayaran gaji untuk <?= htmlspecialchars($p['employee_name']) ?>? Jurnal pengeluaran kas akan otomatis dicatat.')">
                                <i class="bi bi-cash me-1"></i> Bayar
                            </button>
                        </form>
                        <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Slip
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
