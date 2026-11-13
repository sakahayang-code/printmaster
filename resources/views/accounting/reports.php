<?php
use App\Helpers\FormatHelper;
use App\Core\Auth;
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h4 class="fw-bold mb-1"><i class="bi bi-graph-up me-2 text-primary"></i>Laporan Keuangan: Laba Rugi & Neraca</h4>
        <p class="text-muted small mb-0">Laporan keuangan standar akuntansi Indonesia berdasar data riil jurnal umum, pendapatan percetakan, HPP bahan baku, dan beban operasional.</p>
    </div>
    <div class="col-auto">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-pills mb-4 gap-2" id="reportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active px-4 py-2 fw-semibold" id="pnl-tab" data-bs-toggle="tab" data-bs-target="#pnlTabPane" type="button">
            <i class="bi bi-receipt-cutoff me-1"></i> Laba Rugi (Income Statement)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link px-4 py-2 fw-semibold" id="neraca-tab" data-bs-toggle="tab" data-bs-target="#neracaTabPane" type="button">
            <i class="bi bi-bank me-1"></i> Neraca Keuangan (Balance Sheet)
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    <!-- TAB 1: LABA RUGI -->
    <div class="tab-pane fade show active" id="pnlTabPane">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="/accounting/reports" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Periode Mulai</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Periode Selesai</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Tampilkan Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 text-center">
                <h5 class="fw-bold mb-0">LAPORAN LABA RUGI KOMPREHENSIF</h5>
                <small class="text-muted">Periode: <?= FormatHelper::date($startDate) ?> s/d <?= FormatHelper::date($endDate) ?></small>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <!-- PENDAPATAN -->
                            <tr class="table-light">
                                <th colspan="2" class="text-primary fs-6">I. PENDAPATAN OPERASIONAL</th>
                                <th class="text-end text-primary fs-6">Nominal (Rp)</th>
                            </tr>
                            <?php if (empty($pnl['revenues'])): ?>
                            <tr>
                                <td colspan="2" class="ps-4 text-muted fst-italic">Belum ada pendapatan terposting.</td>
                                <td class="text-end text-muted">0</td>
                            </tr>
                            <?php else: foreach ($pnl['revenues'] as $r): ?>
                            <tr>
                                <td style="width: 120px;" class="ps-4 font-monospace text-muted"><?= htmlspecialchars($r['code']) ?></td>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td class="text-end fw-semibold"><?= FormatHelper::rupiah($r['balance']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                            <tr class="border-top">
                                <td colspan="2" class="fw-bold ps-4">TOTAL PENDAPATAN</td>
                                <td class="text-end fw-bold text-success fs-6"><?= FormatHelper::rupiah($pnl['total_revenue']) ?></td>
                            </tr>

                            <!-- HPP -->
                            <tr class="table-light">
                                <th colspan="2" class="text-danger fs-6 pt-4">II. HARGA POKOK PENJUALAN (HPP)</th>
                                <th class="text-end text-danger fs-6 pt-4">Nominal (Rp)</th>
                            </tr>
                            <?php if (empty($pnl['cogs'])): ?>
                            <tr>
                                <td colspan="2" class="ps-4 text-muted fst-italic">Belum ada beban HPP terposting.</td>
                                <td class="text-end text-muted">0</td>
                            </tr>
                            <?php else: foreach ($pnl['cogs'] as $c): ?>
                            <tr>
                                <td class="ps-4 font-monospace text-muted"><?= htmlspecialchars($c['code']) ?></td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td class="text-end fw-semibold"><?= FormatHelper::rupiah($c['balance']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                            <tr class="border-top">
                                <td colspan="2" class="fw-bold ps-4">TOTAL HPP</td>
                                <td class="text-end fw-bold text-danger"><?= FormatHelper::rupiah($pnl['total_cogs']) ?></td>
                            </tr>

                            <!-- LABA KOTOR -->
                            <tr class="bg-primary bg-opacity-10 border-top border-bottom">
                                <td colspan="2" class="fw-bold fs-6 py-3">LABA KOTOR (GROSS PROFIT)</td>
                                <td class="text-end fw-bold fs-6 py-3 text-primary"><?= FormatHelper::rupiah($pnl['gross_profit']) ?></td>
                            </tr>

                            <!-- BEBAN OPERASIONAL -->
                            <tr class="table-light">
                                <th colspan="2" class="text-secondary fs-6 pt-4">III. BEBAN OPERASIONAL</th>
                                <th class="text-end text-secondary fs-6 pt-4">Nominal (Rp)</th>
                            </tr>
                            <?php if (empty($pnl['expenses'])): ?>
                            <tr>
                                <td colspan="2" class="ps-4 text-muted fst-italic">Belum ada beban operasional.</td>
                                <td class="text-end text-muted">0</td>
                            </tr>
                            <?php else: foreach ($pnl['expenses'] as $ex): ?>
                            <tr>
                                <td class="ps-4 font-monospace text-muted"><?= htmlspecialchars($ex['code']) ?></td>
                                <td><?= htmlspecialchars($ex['name']) ?></td>
                                <td class="text-end fw-semibold"><?= FormatHelper::rupiah($ex['balance']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                            <tr class="border-top">
                                <td colspan="2" class="fw-bold ps-4">TOTAL BEBAN OPERASIONAL</td>
                                <td class="text-end fw-bold text-danger"><?= FormatHelper::rupiah($pnl['total_expenses']) ?></td>
                            </tr>

                            <!-- LABA BERSIH -->
                            <tr class="<?= $pnl['net_profit'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white border-top">
                                <td colspan="2" class="fw-bold fs-5 py-3">LABA (RUGI) BERSIH</td>
                                <td class="text-end fw-bold fs-5 py-3"><?= FormatHelper::rupiah($pnl['net_profit']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: NERACA KEUANGAN -->
    <div class="tab-pane fade" id="neracaTabPane">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="/accounting/reports" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Posisi Per Tanggal (As of Date)</label>
                        <input type="date" name="as_of_date" class="form-control form-control-sm" value="<?= htmlspecialchars($asOfDate) ?>">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Tampilkan Posisi Neraca
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <!-- AKTIVA / ASSETS -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2"></i>AKTIVA (ASSETS)</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <?php if (empty($balanceSheet['assets'])): ?>
                                <tr><td class="text-muted">Tidak ada akun aktiva aktif.</td></tr>
                                <?php else: foreach ($balanceSheet['assets'] as $a): ?>
                                <tr>
                                    <td class="text-muted font-monospace small" style="width: 80px;"><?= htmlspecialchars($a['code']) ?></td>
                                    <td><?= htmlspecialchars($a['name']) ?></td>
                                    <td class="text-end fw-semibold"><?= FormatHelper::rupiah($a['balance']) ?></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-top table-light">
                                    <th colspan="2" class="py-2">TOTAL AKTIVA</th>
                                    <th class="text-end py-2 text-primary fs-6"><?= FormatHelper::rupiah($balanceSheet['total_assets']) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PASIVA / LIABILITAS & EKUITAS -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-danger text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>KEWAJIBAN (LIABILITIES)</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <?php if (empty($balanceSheet['liabilities'])): ?>
                                <tr><td class="text-muted">Tidak ada akun kewajiban.</td></tr>
                                <?php else: foreach ($balanceSheet['liabilities'] as $l): ?>
                                <tr>
                                    <td class="text-muted font-monospace small" style="width: 80px;"><?= htmlspecialchars($l['code']) ?></td>
                                    <td><?= htmlspecialchars($l['name']) ?></td>
                                    <td class="text-end fw-semibold"><?= FormatHelper::rupiah($l['balance']) ?></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-top table-light">
                                    <th colspan="2" class="py-2">TOTAL KEWAJIBAN</th>
                                    <th class="text-end py-2 text-danger"><?= FormatHelper::rupiah($balanceSheet['total_liabilities']) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>EKUITAS (EQUITY)</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <?php if (empty($balanceSheet['equity'])): ?>
                                <tr><td class="text-muted">Tidak ada akun ekuitas.</td></tr>
                                <?php else: foreach ($balanceSheet['equity'] as $eq): ?>
                                <tr>
                                    <td class="text-muted font-monospace small" style="width: 80px;"><?= htmlspecialchars($eq['code']) ?></td>
                                    <td><?= htmlspecialchars($eq['name']) ?></td>
                                    <td class="text-end fw-semibold"><?= FormatHelper::rupiah($eq['balance']) ?></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-top table-light">
                                    <th colspan="2" class="py-2">TOTAL EKUITAS</th>
                                    <th class="text-end py-2 text-success"><?= FormatHelper::rupiah($balanceSheet['total_equity']) ?></th>
                                </tr>
                                <tr class="table-dark">
                                    <th colspan="2" class="py-2">TOTAL PASIVA (KEWAJIBAN + EKUITAS)</th>
                                    <th class="text-end py-2 fs-6"><?= FormatHelper::rupiah($balanceSheet['total_liabilities'] + $balanceSheet['total_equity']) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
