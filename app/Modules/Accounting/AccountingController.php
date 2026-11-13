<?php
namespace App\Modules\Accounting;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AccountingService;
use Exception;

class AccountingController extends Controller {

    // ==========================================
    // 1. CASH SHIFTS (SHIFT KASIR)
    // ==========================================
    public function cashShifts() {
        Auth::requirePermission('cash_shift.manage');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND cs.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $sql = "SELECT cs.*, cr.name as register_name, b.name as branch_name, u.name as cashier_name
                FROM cash_shifts cs
                JOIN cash_registers cr ON cs.cash_register_id = cr.id
                JOIN branches b ON cs.branch_id = b.id
                JOIN users u ON cs.user_id = u.id
                WHERE {$where}
                ORDER BY cs.id DESC LIMIT 100";
        $shifts = Database::fetchAll($sql, $params);

        // Check active open shift for current user
        $myOpenShift = Database::fetch(
            "SELECT cs.*, cr.name as register_name 
             FROM cash_shifts cs 
             JOIN cash_registers cr ON cs.cash_register_id = cr.id 
             WHERE cs.user_id = :uid AND cs.status = 'open' LIMIT 1",
            ['uid' => Auth::id()]
        );

        // Available cash registers in active branch
        $registers = Database::fetchAll(
            "SELECT * FROM cash_registers WHERE branch_id = :bid AND is_active = 1",
            ['bid' => $branchId ?: 1]
        );

        $this->view('accounting/cash_shifts', [
            'title'       => 'Shift Kasir & Tutup Kasir',
            'shifts'      => $shifts,
            'myOpenShift' => $myOpenShift,
            'registers'   => $registers
        ]);
    }

    public function openShift() {
        Auth::requirePermission('cash_shift.manage');
        $registerId = (int) Request::input('cash_register_id');
        $openingBalance = (float) Request::input('opening_balance', 0);
        $notes = Request::input('notes');

        // Check if user already has open shift
        $existing = Database::fetch(
            "SELECT id FROM cash_shifts WHERE user_id = :uid AND status = 'open' LIMIT 1",
            ['uid' => Auth::id()]
        );
        if ($existing) {
            Session::flash('error', 'Anda masih memiliki shift yang berstatus buka. Silakan tutup shift terlebih dahulu.');
            Response::redirect('/cash-shifts');
        }

        Database::insert('cash_shifts', [
            'branch_id'        => Auth::activeBranchId() ?: 1,
            'cash_register_id' => $registerId,
            'user_id'          => Auth::id(),
            'opening_time'     => date('Y-m-d H:i:s'),
            'opening_balance'  => $openingBalance,
            'expected_balance' => $openingBalance,
            'actual_balance'   => $openingBalance,
            'difference'       => 0,
            'status'           => 'open',
            'notes'            => $notes
        ]);

        Session::flash('success', 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($openingBalance, 0, ',', '.'));
        Response::redirect('/cash-shifts');
    }

    public function closeShift() {
        Auth::requirePermission('cash_shift.manage');
        $shiftId = (int) Request::input('shift_id');
        $actualBalance = (float) Request::input('actual_balance', 0);
        $notes = Request::input('notes');

        $shift = Database::fetch("SELECT * FROM cash_shifts WHERE id = :id AND status = 'open'", ['id' => $shiftId]);
        if (!$shift) {
            Session::flash('error', 'Shift tidak ditemukan atau sudah ditutup.');
            Response::redirect('/cash-shifts');
        }

        // Calculate cash sales during this shift for this user
        $cashSales = (float) Database::fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments 
             WHERE created_by = :uid AND payment_method = 'cash' 
             AND payment_date >= :open_time AND status = 'verified'",
            ['uid' => $shift['user_id'], 'open_time' => $shift['opening_time']]
        );

        $expectedBalance = (float) $shift['opening_balance'] + $cashSales;
        $difference = $actualBalance - $expectedBalance;

        Database::update('cash_shifts', [
            'closing_time'     => date('Y-m-d H:i:s'),
            'cash_sales'       => $cashSales,
            'expected_balance' => $expectedBalance,
            'actual_balance'   => $actualBalance,
            'difference'       => $difference,
            'status'           => 'closed',
            'notes'            => trim($shift['notes'] . " | Penutupan: " . $notes),
            'approved_by'      => Auth::id()
        ], 'id = :id', ['id' => $shiftId]);

        Session::flash('success', 'Shift kasir berhasil ditutup. Total penjualan tunai: Rp ' . number_format($cashSales, 0, ',', '.') . ' (Selisih: Rp ' . number_format($difference, 0, ',', '.') . ')');
        Response::redirect('/cash-shifts');
    }

    // ==========================================
    // 2. PAYMENTS & RECEIVABLES (PEMBAYARAN)
    // ==========================================
    public function payments() {
        Auth::requirePermission('payments.view');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND p.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $method = Request::input('method');
        if ($method) {
            $where .= " AND p.payment_method = :method";
            $params['method'] = $method;
        }

        $status = Request::input('status');
        if ($status) {
            $where .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT p.*, o.order_number, c.name as customer_name, b.name as branch_name, u.name as cashier_name
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.id
                LEFT JOIN customers c ON o.customer_id = c.id
                JOIN branches b ON p.branch_id = b.id
                JOIN users u ON p.created_by = u.id
                WHERE {$where}
                ORDER BY p.id DESC LIMIT 100";
        $payments = Database::fetchAll($sql, $params);

        // Total summary
        $totalPaid = Database::fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments p WHERE {$where} AND p.status = 'verified'",
            $params
        );

        $this->view('accounting/payments', [
            'title'     => 'Data Pembayaran & Transaksi',
            'payments'  => $payments,
            'totalPaid' => $totalPaid,
            'method'    => $method,
            'status'    => $status
        ]);
    }

    public function verifyPayment() {
        Auth::requirePermission('payments.view');
        $paymentId = (int) Request::input('payment_id');
        $status = Request::input('status', 'verified');

        Database::update('payments', [
            'status'      => $status,
            'verified_by' => Auth::id()
        ], 'id = :id', ['id' => $paymentId]);

        Session::flash('success', "Status pembayaran #{$paymentId} berhasil diperbarui menjadi {$status}.");
        Response::redirect('/payments');
    }

    // ==========================================
    // 3. EXPENSES & CASH OUT (BIAYA OPERASIONAL)
    // ==========================================
    public function expenses() {
        Auth::requirePermission('payments.view');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND e.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $sql = "SELECT e.*, b.name as branch_name, a.name as account_name, u.name as user_name
                FROM expenses e
                JOIN branches b ON e.branch_id = b.id
                LEFT JOIN accounts a ON e.account_id = a.id
                JOIN users u ON e.created_by = u.id
                WHERE {$where}
                ORDER BY e.expense_date DESC, e.id DESC LIMIT 100";
        $expenses = Database::fetchAll($sql, $params);

        // Expense accounts for dropdown (code 6xxx)
        $expenseAccounts = Database::fetchAll("SELECT * FROM accounts WHERE code LIKE '6%' AND is_active = 1 ORDER BY code ASC");

        $totalMonth = Database::fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE branch_id = :bid AND expense_date >= :first_day",
            ['bid' => $branchId ?: 1, 'first_day' => date('Y-m-01')]
        );

        $this->view('accounting/expenses', [
            'title'           => 'Biaya & Kas Keluar',
            'expenses'        => $expenses,
            'expenseAccounts' => $expenseAccounts,
            'totalMonth'      => (float) $totalMonth
        ]);
    }

    public function storeExpense() {
        Auth::requirePermission('payments.view');
        $branchId = Auth::activeBranchId() ?: 1;
        $category = Request::input('category');
        $accountId = (int) Request::input('account_id');
        $amount = (float) Request::input('amount');
        $paidTo = Request::input('paid_to');
        $description = Request::input('description');
        $paymentMethod = Request::input('payment_method', 'cash');
        $expenseDate = Request::input('expense_date', date('Y-m-d'));

        if ($amount <= 0 || empty($paidTo) || empty($description)) {
            Session::flash('error', 'Semua data pengeluaran wajib diisi dengan benar.');
            Response::redirect('/expenses');
        }

        // Expense number EXP-YYYYMM-XXXX
        $ym = date('Ym', strtotime($expenseDate));
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM expenses WHERE expense_number LIKE 'EXP-{$ym}-%'");
        $expenseNumber = "EXP-{$ym}-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        Database::beginTransaction();
        try {
            $expenseId = Database::insert('expenses', [
                'company_id'     => 1,
                'branch_id'      => $branchId,
                'expense_number' => $expenseNumber,
                'expense_date'   => $expenseDate,
                'category'       => $category,
                'amount'         => $amount,
                'payment_method' => $paymentMethod,
                'account_id'     => $accountId ?: 15, // fallback to Beban Operasional Lainnya
                'paid_to'        => $paidTo,
                'description'    => $description,
                'approved_by'    => Auth::id(),
                'created_by'     => Auth::id()
            ]);

            // Auto-post journal for expense
            // Debit: Expense Account
            // Credit: Cash (1001) or Bank (1002)
            $cashAccount = Database::fetch("SELECT id FROM accounts WHERE code = '1001' LIMIT 1");
            $bankAccount = Database::fetch("SELECT id FROM accounts WHERE code = '1002' LIMIT 1");
            $creditAccId = ($paymentMethod === 'bank_transfer' && $bankAccount) ? (int)$bankAccount['id'] : ((int)($cashAccount['id'] ?? 1));

            AccountingService::postJournal(
                $branchId,
                $expenseDate,
                'expense',
                $expenseId,
                "Biaya Operasional [{$expenseNumber}]: {$description} (Kepada: {$paidTo})",
                [
                    ['account_id' => $accountId ?: 15, 'debit' => $amount, 'credit' => 0, 'memo' => $description],
                    ['account_id' => $creditAccId, 'debit' => 0, 'credit' => $amount, 'memo' => "Pembayaran via {$paymentMethod}"]
                ],
                Auth::id()
            );

            Database::commit();
            Session::flash('success', "Pengeluaran {$expenseNumber} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat dan dijurnal.");
        } catch (Exception $e) {
            Database::rollBack();
            Session::flash('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }

        Response::redirect('/expenses');
    }

    // ==========================================
    // 4. GENERAL JOURNALS (JURNAL UMUM)
    // ==========================================
    public function journals() {
        Auth::requirePermission('accounting.view');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND j.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $sql = "SELECT j.*, b.name as branch_name, u.name as author_name
                FROM journals j
                JOIN branches b ON j.branch_id = b.id
                JOIN users u ON j.created_by = u.id
                WHERE {$where}
                ORDER BY j.journal_date DESC, j.id DESC LIMIT 50";
        $journals = Database::fetchAll($sql, $params);

        // Fetch details for all journals in query
        if (!empty($journals)) {
            $journalIds = array_column($journals, 'id');
            $inClause = implode(',', array_map('intval', $journalIds));
            $details = Database::fetchAll(
                "SELECT jd.*, a.code as account_code, a.name as account_name
                 FROM journal_details jd
                 JOIN accounts a ON jd.account_id = a.id
                 WHERE jd.journal_id IN ({$inClause})
                 ORDER BY jd.id ASC"
            );
            $grouped = [];
            foreach ($details as $d) {
                $grouped[$d['journal_id']][] = $d;
            }
            foreach ($journals as &$j) {
                $j['details'] = $grouped[$j['id']] ?? [];
            }
        }

        // All COA accounts for manual journal modal
        $accounts = Database::fetchAll("SELECT * FROM accounts WHERE is_active = 1 ORDER BY code ASC");

        $this->view('accounting/journals', [
            'title'    => 'Buku Jurnal Umum (General Journal)',
            'journals' => $journals,
            'accounts' => $accounts
        ]);
    }

    public function storeJournal() {
        Auth::requirePermission('accounting.view');
        $branchId = Auth::activeBranchId() ?: 1;
        $journalDate = Request::input('journal_date', date('Y-m-d'));
        $description = Request::input('description');
        $accountIds = Request::input('account_id', []);
        $debits = Request::input('debit', []);
        $credits = Request::input('credit', []);
        $memos = Request::input('memo', []);

        if (empty($description) || empty($accountIds) || count($accountIds) < 2) {
            Session::flash('error', 'Jurnal harus memiliki minimal 2 baris akun dan deskripsi.');
            Response::redirect('/accounting/journals');
        }

        $entries = [];
        for ($i = 0; $i < count($accountIds); $i++) {
            $accId = (int) $accountIds[$i];
            $deb = (float) ($debits[$i] ?? 0);
            $crd = (float) ($credits[$i] ?? 0);
            if ($accId > 0 && ($deb > 0 || $crd > 0)) {
                $entries[] = [
                    'account_id' => $accId,
                    'debit'      => $deb,
                    'credit'     => $crd,
                    'memo'       => $memos[$i] ?? $description
                ];
            }
        }

        try {
            AccountingService::postJournal(
                $branchId,
                $journalDate,
                'manual',
                null,
                $description,
                $entries,
                Auth::id()
            );
            Session::flash('success', 'Jurnal umum berhasil diposting dan saldo seimbang (Balanced).');
        } catch (Exception $e) {
            Session::flash('error', 'Gagal posting jurnal: ' . $e->getMessage());
        }

        Response::redirect('/accounting/journals');
    }

    // ==========================================
    // 5. FINANCIAL STATEMENTS (LABA RUGI & NERACA)
    // ==========================================
    public function reports() {
        Auth::requirePermission('accounting.view');
        $branchId = Auth::activeBranchId();

        $startDate = Request::input('start_date', date('Y-m-01'));
        $endDate = Request::input('end_date', date('Y-m-d'));
        $asOfDate = Request::input('as_of_date', date('Y-m-d'));

        // Income Statement
        $pnl = AccountingService::getIncomeStatement($branchId, $startDate, $endDate);

        // Balance Sheet
        $balanceSheet = AccountingService::getBalanceSheet($branchId, $asOfDate);

        $this->view('accounting/reports', [
            'title'        => 'Laporan Keuangan: Laba Rugi & Neraca',
            'pnl'          => $pnl,
            'balanceSheet' => $balanceSheet,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'asOfDate'     => $asOfDate
        ]);
    }
}
