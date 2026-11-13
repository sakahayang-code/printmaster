<?php
use App\Helpers\FormatHelper;
use App\Core\CSRF;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Buku Jurnal Umum (General Journal)</h4>
        <p class="text-muted small mb-0">Catatan transaksi akuntansi double-entry (Debit = Kredit) otomatis dari penjualan, pembelian, biaya, dan penyesuaian manual.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newJournalModal">
            <i class="bi bi-plus-lg me-1"></i> Buat Jurnal Manual
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">No. Jurnal</th>
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 120px;">Cabang</th>
                    <th>Keterangan / Rincian Akun</th>
                    <th class="text-end" style="width: 140px;">Debit</th>
                    <th class="text-end" style="width: 140px;">Kredit</th>
                    <th class="text-center" style="width: 90px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($journals)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada catatan jurnal umum.</td>
                </tr>
                <?php else: foreach ($journals as $j): ?>
                <tr class="table-light bg-opacity-50">
                    <td><strong class="text-primary font-monospace"><?= htmlspecialchars($j['journal_number']) ?></strong></td>
                    <td class="small fw-semibold"><?= FormatHelper::date($j['journal_date']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($j['branch_name']) ?></span></td>
                    <td class="fw-semibold">
                        <?= htmlspecialchars($j['description']) ?>
                        <span class="badge bg-secondary-subtle text-secondary ms-2 small"><?= htmlspecialchars($j['reference_type'] ?? 'manual') ?></span>
                    </td>
                    <td class="text-end fw-bold text-dark"><?= FormatHelper::rupiah($j['total_debit']) ?></td>
                    <td class="text-end fw-bold text-dark"><?= FormatHelper::rupiah($j['total_credit']) ?></td>
                    <td class="text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <?= ucfirst(htmlspecialchars($j['status'])) ?>
                        </span>
                    </td>
                </tr>
                <!-- Sub-rows for journal detail lines -->
                <?php if (!empty($j['details'])): foreach ($j['details'] as $line): ?>
                <tr>
                    <td colspan="3" class="border-0"></td>
                    <td class="ps-4 small">
                        <?php if ($line['credit'] > 0): ?>
                        <div class="ps-3 text-secondary">
                            <i class="bi bi-arrow-return-right me-1"></i>
                            <strong><?= htmlspecialchars($line['account_code']) ?></strong> - <?= htmlspecialchars($line['account_name']) ?>
                            <?php if ($line['memo']): ?><span class="text-muted fst-italic ms-1">(<?= htmlspecialchars($line['memo']) ?>)</span><?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div>
                            <strong><?= htmlspecialchars($line['account_code']) ?></strong> - <?= htmlspecialchars($line['account_name']) ?>
                            <?php if ($line['memo']): ?><span class="text-muted fst-italic ms-1">(<?= htmlspecialchars($line['memo']) ?>)</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-muted small">
                        <?= $line['debit'] > 0 ? FormatHelper::rupiah($line['debit']) : '-' ?>
                    </td>
                    <td class="text-end text-muted small">
                        <?= $line['credit'] > 0 ? FormatHelper::rupiah($line['credit']) : '-' ?>
                    </td>
                    <td class="border-0"></td>
                </tr>
                <?php endforeach; endif; ?>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Jurnal Manual -->
<div class="modal fade" id="newJournalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/accounting/journals/store" class="modal-content">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2 text-primary"></i>Buat Jurnal Umum Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Jurnal</label>
                        <input type="date" name="journal_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Deskripsi / Memo Transaksi</label>
                        <input type="text" name="description" class="form-control" placeholder="Contoh: Penyesuaian Saldo Kas, Setoran Modal" required>
                    </div>
                </div>

                <div class="border rounded-3 p-3 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Rincian Pos Jurnal (Min 2 Baris)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div id="journalRows">
                        <!-- Baris 1: Debit -->
                        <div class="row g-2 align-items-center mb-2 journal-line">
                            <div class="col-md-5">
                                <select name="account_id[]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Akun --</option>
                                    <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code']) ?> - <?= htmlspecialchars($a['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="debit[]" class="form-control form-select-sm deb-input" placeholder="Debit (Rp)" min="0" step="500" value="0">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="credit[]" class="form-control form-select-sm crd-input" placeholder="Kredit (Rp)" min="0" step="500" value="0">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm text-danger disabled"><i class="bi bi-x-circle"></i></button>
                            </div>
                        </div>
                        <!-- Baris 2: Kredit -->
                        <div class="row g-2 align-items-center mb-2 journal-line">
                            <div class="col-md-5">
                                <select name="account_id[]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Akun --</option>
                                    <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code']) ?> - <?= htmlspecialchars($a['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="debit[]" class="form-control form-select-sm deb-input" placeholder="Debit (Rp)" min="0" step="500" value="0">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="credit[]" class="form-control form-select-sm crd-input" placeholder="Kredit (Rp)" min="0" step="500" value="0">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm text-danger disabled"><i class="bi bi-x-circle"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between p-2 bg-white rounded border">
                    <div>Status Keseimbangan: <span id="balanceBadge" class="badge bg-secondary">Periksa Saldo</span></div>
                    <div class="fw-bold">
                        Total Debit: <span id="totDeb" class="text-primary me-3">Rp 0</span>
                        Total Kredit: <span id="totCrd" class="text-success">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="saveJournalBtn" class="btn btn-primary" disabled><i class="bi bi-check2-circle me-1"></i> Simpan Jurnal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsContainer = document.getElementById('journalRows');
    const addRowBtn = document.getElementById('addRowBtn');
    const totDebEl = document.getElementById('totDeb');
    const totCrdEl = document.getElementById('totCrd');
    const balanceBadge = document.getElementById('balanceBadge');
    const saveBtn = document.getElementById('saveJournalBtn');

    function recalc() {
        let debSum = 0;
        let crdSum = 0;
        document.querySelectorAll('.deb-input').forEach(i => debSum += parseFloat(i.value || 0));
        document.querySelectorAll('.crd-input').forEach(i => crdSum += parseFloat(i.value || 0));

        totDebEl.innerText = 'Rp ' + debSum.toLocaleString('id-ID');
        totCrdEl.innerText = 'Rp ' + crdSum.toLocaleString('id-ID');

        if (debSum > 0 && Math.abs(debSum - crdSum) < 0.01) {
            balanceBadge.className = 'badge bg-success';
            balanceBadge.innerText = 'Seimbang (Balanced)';
            saveBtn.disabled = false;
        } else {
            balanceBadge.className = 'badge bg-danger';
            balanceBadge.innerText = 'Belum Seimbang (Selisih Rp ' + Math.abs(debSum - crdSum).toLocaleString('id-ID') + ')';
            saveBtn.disabled = true;
        }
    }

    rowsContainer.addEventListener('input', recalc);

    addRowBtn.addEventListener('click', function() {
        const firstLine = rowsContainer.querySelector('.journal-line');
        const clone = firstLine.cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => inp.value = 0);
        clone.querySelector('select').selectedIndex = 0;
        const removeBtn = clone.querySelector('button');
        removeBtn.classList.remove('disabled');
        removeBtn.onclick = function() {
            clone.remove();
            recalc();
        };
        rowsContainer.appendChild(clone);
    });
});
</script>
