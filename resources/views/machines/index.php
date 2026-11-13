<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
use App\Core\Auth;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Manajemen Mesin & Pemeliharaan</h3>
        <p class="text-muted small mb-0">Monitor status ketersediaan mesin cetak, meteran counter, jadwal servis berkala, dan beban biaya spare part.</p>
    </div>
    <?php if (Auth::hasPermission('machines.manage')): ?>
    <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMachine">
        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Mesin Baru
    </button>
    <?php endif; ?>
</div>

<ul class="nav nav-pills mb-3" id="machineTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="tab-machines-btn" data-bs-toggle="pill" data-bs-target="#tab-machines">
            <i class="bi bi-cpu me-1"></i> Mesin Produksi (<?= count($machines) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="tab-maint-btn" data-bs-toggle="pill" data-bs-target="#tab-maint">
            <i class="bi bi-tools me-1"></i> Riwayat Servis & Pemeliharaan (<?= count($maintenanceLogs) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: DAFTAR MESIN -->
    <div class="tab-pane fade show active" id="tab-machines">
        <div class="row g-3">
            <?php foreach ($machines as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-custom h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-secondary border"><?= htmlspecialchars($m['code']) ?></span>
                        <?php
                        $statusBadges = [
                            'available'   => ['bg-success', 'Tersedia (Ready)'],
                            'running'     => ['bg-primary', 'Beroperasi Cetak'],
                            'maintenance' => ['bg-warning text-dark', 'Pemeliharaan'],
                            'broken'      => ['bg-danger', 'Rusak / Rusak Mesin']
                        ];
                        $sb = $statusBadges[$m['status']] ?? ['bg-secondary', ucfirst($m['status'])];
                        ?>
                        <span class="badge <?= $sb[0] ?>"><?= $sb[1] ?></span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($m['name']) ?></h5>
                    <div class="text-muted small mb-3"><?= htmlspecialchars($m['brand_model'] ?? 'Mesin Industri Percetakan') ?></div>

                    <div class="bg-light p-2 rounded small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tipe Mesin:</span>
                            <span class="fw-semibold text-uppercase"><?= str_replace('_', ' ', $m['machine_type']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Kapasitas / Jam:</span>
                            <span class="fw-semibold"><?= number_format($m['capacity_per_hour']) ?> unit</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Counter Meter Jalan:</span>
                            <span class="fw-bold text-primary"><?= number_format($m['meter_counter'], 1) ?> m</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Biaya Servis:</span>
                            <span class="fw-semibold text-danger"><?= FormatHelper::rupiah($m['total_maintenance_cost']) ?></span>
                        </div>
                    </div>

                    <div class="mt-auto d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary w-50" onclick="openUpdateMachineStatus(<?= $m['id'] ?>, '<?= $m['status'] ?>', <?= $m['meter_counter'] ?>)">
                            <i class="bi bi-toggles me-1"></i> Status
                        </button>
                        <button class="btn btn-sm btn-outline-warning text-dark w-50" onclick="openLogMaintenance(<?= $m['id'] ?>, '<?= htmlspecialchars($m['name']) ?>')">
                            <i class="bi bi-tools me-1"></i> Catat Servis
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- TAB 2: RIWAYAT PEMELIHARAAN -->
    <div class="tab-pane fade" id="tab-maint">
        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Mesin</th>
                                <th>Tanggal Servis</th>
                                <th>Tipe Servis</th>
                                <th>Keterangan / Penggantian Sparepart</th>
                                <th>Biaya</th>
                                <th>Teknisi / Pelaksana</th>
                                <th>Jadwal Servis Berikutnya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($maintenanceLogs)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada catatan pemeliharaan mesin.</td></tr>
                            <?php else: ?>
                                <?php foreach ($maintenanceLogs as $ml): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($ml['machine_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($ml['machine_code']) ?></small>
                                    </td>
                                    <td><?= FormatHelper::tanggal($ml['maintenance_date'], false) ?></td>
                                    <td><span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', $ml['maintenance_type'])) ?></span></td>
                                    <td><?= htmlspecialchars($ml['description']) ?></td>
                                    <td class="fw-bold text-danger"><?= FormatHelper::rupiah($ml['cost']) ?></td>
                                    <td><?= htmlspecialchars($ml['performed_by']) ?></td>
                                    <td><?= FormatHelper::tanggal($ml['next_schedule'], false) ?></td>
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

<!-- MODAL TAMBAH MESIN -->
<div class="modal fade" id="modalAddMachine" tabindex="-1">
    <div class="modal-dialog">
        <form action="/machines/store" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Mesin Percetakan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Mesin <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Mesin Outdoor Flora 3.2m" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Merk & Model</label>
                    <input type="text" name="brand_model" class="form-control" placeholder="Contoh: Konica 512i High Speed">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipe Mesin Percetakan</label>
                    <select name="machine_type" class="form-select">
                        <option value="outdoor_flexy">Outdoor Banner / Spanduk</option>
                        <option value="indoor_photo">Indoor Photo / Eco-Solvent</option>
                        <option value="digital_a3">Digital Laser A3+</option>
                        <option value="offset">Offset Percetakan</option>
                        <option value="cutting_plotter">Cutting Plotter</option>
                        <option value="laminator">Laminator Panas/Dingin</option>
                        <option value="binding">Penjilidan / Binding</option>
                        <option value="finishing">Finishing / Pond</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Kapasitas / Jam</label>
                        <input type="number" name="capacity_per_hour" class="form-control" value="60">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Biaya Operasional/Jam</label>
                        <input type="number" name="hourly_cost" class="form-control" value="50000">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Counter Meter Awal</label>
                    <input type="number" name="meter_counter" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Mesin</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL UPDATE STATUS MESIN -->
<div class="modal fade" id="modalMachineStatus" tabindex="-1">
    <div class="modal-dialog">
        <form id="formMachineStatus" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Update Status & Counter Mesin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status Operasional Mesin</label>
                    <select name="status" id="selMachineStatus" class="form-select">
                        <option value="available">Tersedia (Ready for Jobs)</option>
                        <option value="running">Sedang Beroperasi Cetak</option>
                        <option value="maintenance">Dalam Pemeliharaan / Servis</option>
                        <option value="broken">Rusak / Tidak Dapat Digunakan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Counter Meteran Saat Ini (m)</label>
                    <input type="number" name="meter_counter" id="inpMeterCounter" class="form-control" step="0.1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CATAT PEMELIHARAAN -->
<div class="modal fade" id="modalLogMaint" tabindex="-1">
    <div class="modal-dialog">
        <form id="formLogMaint" method="POST" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-tools me-2"></i>Catat Pemeliharaan / Servis Mesin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="fw-bold mb-3 text-primary" id="txtMaintMachineName">Mesin: -</div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tanggal Servis</label>
                        <input type="date" name="maintenance_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tipe Pemeliharaan</label>
                        <select name="maintenance_type" class="form-select">
                            <option value="routine">Servis Rutin / Cleaning Head</option>
                            <option value="repair">Perbaikan Kerusakan</option>
                            <option value="sparepart_replace">Penggantian Sparepart</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Biaya Pemeliharaan / Sparepart (Rp)</label>
                    <input type="number" name="cost" class="form-control" value="0" min="0" step="5000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Keterangan Pekerjaan Servis</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Penggantian filter tinta, pembersihan sensor, kalibrasi..." required></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Teknisi / Pelaksana</label>
                        <input type="text" name="performed_by" class="form-control" value="Teknisi Vendor">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Jadwal Servis Berikutnya</label>
                        <input type="date" name="next_schedule" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning fw-bold">Simpan Catatan Servis</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateMachineStatus(id, st, cnt) {
    document.getElementById('formMachineStatus').action = '/machines/' + id + '/status';
    document.getElementById('selMachineStatus').value = st;
    document.getElementById('inpMeterCounter').value = cnt;
    new bootstrap.Modal(document.getElementById('modalMachineStatus')).show();
}

function openLogMaintenance(id, name) {
    document.getElementById('formLogMaint').action = '/machines/' + id + '/maintenance';
    document.getElementById('txtMaintMachineName').innerText = 'Mesin: ' + name;
    new bootstrap.Modal(document.getElementById('modalLogMaint')).show();
}
</script>
