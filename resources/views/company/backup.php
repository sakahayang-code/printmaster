<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-database-check text-primary me-2"></i>Cadangan & Pemulihan Basis Data</h4>
            <p class="text-muted small mb-0">Manajemen snapshot data, pencegahan kehilangan data saat pembaruan, dan pemulihan sistem (Backup & Disaster Recovery).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/backup/download" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-cloud-arrow-down me-1"></i>Unduh Dump SQL Langsung
            </a>
            <form action="/backup/create" method="POST" class="d-inline">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-save2 me-1"></i>Buat Cadangan Baru Sekarang
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Keamanan Data -->
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3" role="alert">
        <i class="bi bi-shield-fill-check fs-2 text-success me-3"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Proteksi Keamanan Data Aktif</h6>
            <div class="small text-muted">
                Aplikasi menerapkan protokol <strong>Non-Destructive Schema</strong>. Pembaruan kode atau penambahan modul di masa mendatang tidak akan menghapus data transaksi, pelanggan, atau keuangan yang telah tersimpan. Seluruh berkas cadangan disimpan aman di folder <code>storage/backups/</code>.
            </div>
        </div>
    </div>

    <!-- Statistik Metrik Database -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Nama Database</span>
                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= htmlspecialchars($dbName) ?></h5>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-hdd-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Tabel Relasional</span>
                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= number_format($tableCount) ?> Tabel</h5>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-table fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-info border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Ukuran Data Fisik</span>
                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= $totalSizeMb ?> MB</h5>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="bi bi-pie-chart fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Berkas Cadangan</span>
                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= count($backups) ?> Snapshot</h5>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-archive fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Riwayat Berkas Cadangan -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Daftar Berkas Cadangan (Snapshots)</h6>
                    <span class="badge bg-light text-muted border"><?= count($backups) ?> berkas tersedia</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($backups)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-archive display-4 text-secondary mb-2"></i>
                        <p class="mb-2">Belum ada berkas cadangan yang tersimpan di server.</p>
                        <form action="/backup/create" method="POST" class="d-inline">
                            <?= \App\Core\CSRF::field() ?>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>Buat Cadangan Pertama Sekarang
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">Nama Berkas Cadangan</th>
                                    <th>Tipe</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal Pembuatan</th>
                                    <th class="text-end pe-3">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $b): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-filetype-sql fs-4 text-primary me-2"></i>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($b['filename']) ?></div>
                                                <small class="text-muted">Format SQL Relasional (Safe / Non-destructive)</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($b['is_auto']): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-robot me-1"></i>Otomatis Cron</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-hand-index me-1"></i>Manual User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= $b['size_human'] ?></span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($b['created_at']) ?></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/backup/file/<?= urlencode($b['filename']) ?>" class="btn btn-outline-primary" title="Unduh ke Komputer">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning" title="Pulihkan / Restore" onclick="confirmRestore('<?= htmlspecialchars($b['filename']) ?>')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <form action="/backup/delete" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus berkas cadangan ini?');">
                                                <?= \App\Core\CSRF::field() ?>
                                                <input type="hidden" name="filename" value="<?= htmlspecialchars($b['filename']) ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus Berkas">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Migrasi, Restore Upload & Jadwal Otomatis -->
        <div class="col-lg-4">
            <!-- Card Migrasi Database -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-diagram-3 text-success me-2"></i>Status Migrasi Struktur</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingMigrations)): ?>
                    <div class="d-flex align-items-center text-success mb-2">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <span class="fw-semibold">Struktur Database Mutakhir</span>
                    </div>
                    <p class="text-muted small mb-0">Seluruh tabel dan skema telah sesuai dengan kode aplikasi terbaru. Tidak ada perubahan yang tertunda.</p>
                    <?php else: ?>
                    <div class="alert alert-warning py-2 px-3 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat <strong><?= count($pendingMigrations) ?></strong> pembaruan skema tertunda.
                    </div>
                    <form action="/backup/migrate" method="POST">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-play-circle me-1"></i>Jalankan Migrasi Tertunda
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Unggah & Pulihkan (Restore) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cloud-arrow-up text-warning me-2"></i>Pulihkan dari Berkas Eksternal</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Punya berkas <code>.sql</code> cadangan dari komputer lokal atau server lain? Anda dapat mengimpornya kembali di sini.</p>
                    <form action="/backup/restore" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN: Memulihkan database akan memperbarui isi data tabel. Pastikan Anda telah membuat cadangan terbaru terlebih dahulu. Lanjutkan?');">
                        <?= \App\Core\CSRF::field() ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pilih Berkas SQL (*.sql)</label>
                            <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-semibold text-dark">
                            <i class="bi bi-arrow-repeat me-1"></i>Unggah & Pulihkan Database
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card Pengaturan Cron Job Otomatis -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-alarm text-info me-2"></i>Jadwal Cadangan Otomatis</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Untuk membuat cadangan otomatis setiap hari tanpa harus membuka web, pasang perintah ini di menu <strong>Cron Jobs</strong> cPanel:</p>
                    <div class="bg-light p-2 rounded border small font-monospace text-break mb-2 user-select-all">
                        0 2 * * * php -q <?= dirname(__DIR__, 3) ?>/cli/backup.php >/dev/null 2>&1
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Perintah di atas akan mencadangkan seluruh data setiap pukul 02:00 pagi WIB dan menghapus otomatis cadangan yang berusia di atas 30 hari.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Restore Snapshot -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/backup/restore" method="POST">
            <?= \App\Core\CSRF::field() ?>
            <input type="hidden" name="existing_file" id="restoreFilename">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning-subtle text-dark border-0">
                    <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Konfirmasi Pemulihan Database</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan memulihkan basis data menggunakan snapshot berikut:</p>
                    <div class="alert alert-light border py-2 px-3 fw-bold text-primary mb-3" id="restoreFilenameLabel"></div>
                    <div class="alert alert-danger small mb-0">
                        <i class="bi bi-shield-exclamation me-1"></i><strong>Perhatian:</strong> Proses ini akan menyelaraskan data dengan titik waktu saat cadangan tersebut dibuat. Disarankan membuat cadangan baru sebelum melakukan tindakan ini.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold text-dark">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Ya, Pulihkan Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function confirmRestore(filename) {
    document.getElementById('restoreFilename').value = filename;
    document.getElementById('restoreFilenameLabel').innerText = filename;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}
</script>
