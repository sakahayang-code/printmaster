<?php
namespace App\Services;

use App\Core\Database;
use Exception;

class AccountingService {
    /**
     * Post a balanced journal entry.
     * $entries is an array of ['account_id' => int, 'debit' => float, 'credit' => float, 'memo' => string]
     */
    public static function postJournal(
        int $branchId,
        string $journalDate,
        string $refType,
        ?int $refId,
        string $description,
        array $entries,
        int $userId,
        int $companyId = 1
    ): int {
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($entries as $e) {
            $totalDebit += (float) ($e['debit'] ?? 0);
            $totalCredit += (float) ($e['credit'] ?? 0);
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception("Jurnal tidak seimbang! Total Debit: {$totalDebit}, Total Kredit: {$totalCredit}");
        }

        // Generate Journal Number JRN-YYYYMM-XXXX
        $ym = date('Ym', strtotime($journalDate));
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM journals WHERE journal_number LIKE 'JRN-{$ym}-%'");
        $seq = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $journalNumber = "JRN-{$ym}-{$seq}";

        Database::beginTransaction();
        try {
            $journalId = Database::insert('journals', [
                'company_id'     => $companyId,
                'branch_id'      => $branchId,
                'journal_number' => $journalNumber,
                'journal_date'   => $journalDate,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => $description,
                'total_debit'    => $totalDebit,
                'total_credit'   => $totalCredit,
                'status'         => 'posted',
                'created_by'     => $userId
            ]);

            foreach ($entries as $item) {
                Database::insert('journal_details', [
                    'journal_id' => $journalId,
                    'account_id' => (int) $item['account_id'],
                    'debit'      => (float) ($item['debit'] ?? 0),
                    'credit'     => (float) ($item['credit'] ?? 0),
                    'memo'       => $item['memo'] ?? null
                ]);
            }

            Database::commit();
            return $journalId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Generates Profit & Loss Statement (Laporan Laba Rugi)
     */
    public static function getIncomeStatement(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array {
        $startDate = $startDate ?: date('Y-m-01');
        $endDate   = $endDate ?: date('Y-m-d');

        $where = "j.journal_date BETWEEN :start AND :end AND j.status = 'posted'";
        $params = ['start' => $startDate, 'end' => $endDate];

        if ($branchId !== null && $branchId > 0) {
            $where .= " AND j.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        // Revenues
        $sqlRev = "SELECT a.id, a.code, a.name, SUM(jd.credit - jd.debit) as balance
                   FROM accounts a
                   JOIN journal_details jd ON a.id = jd.account_id
                   JOIN journals j ON jd.journal_id = j.id
                   WHERE a.type = 'revenue' AND {$where}
                   GROUP BY a.id, a.code, a.name
                   HAVING balance != 0
                   ORDER BY a.code ASC";
        $revenues = Database::fetchAll($sqlRev, $params);
        $totalRevenue = array_sum(array_column($revenues, 'balance'));

        // COGS (HPP) - Account code 5xxx
        $sqlCogs = "SELECT a.id, a.code, a.name, SUM(jd.debit - jd.credit) as balance
                    FROM accounts a
                    JOIN journal_details jd ON a.id = jd.account_id
                    JOIN journals j ON jd.journal_id = j.id
                    WHERE a.type = 'expense' AND a.code LIKE '5%' AND {$where}
                    GROUP BY a.id, a.code, a.name
                    HAVING balance != 0
                    ORDER BY a.code ASC";
        $cogs = Database::fetchAll($sqlCogs, $params);
        $totalCogs = array_sum(array_column($cogs, 'balance'));

        $grossProfit = $totalRevenue - $totalCogs;

        // Operating Expenses - Account code 6xxx
        $sqlExp = "SELECT a.id, a.code, a.name, SUM(jd.debit - jd.credit) as balance
                   FROM accounts a
                   JOIN journal_details jd ON a.id = jd.account_id
                   JOIN journals j ON jd.journal_id = j.id
                   WHERE a.type = 'expense' AND a.code LIKE '6%' AND {$where}
                   GROUP BY a.id, a.code, a.name
                   HAVING balance != 0
                   ORDER BY a.code ASC";
        $expenses = Database::fetchAll($sqlExp, $params);
        $totalExpenses = array_sum(array_column($expenses, 'balance'));

        $netProfit = $grossProfit - $totalExpenses;

        return [
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'branch_id'      => $branchId,
            'revenues'       => $revenues,
            'total_revenue'  => (float) $totalRevenue,
            'cogs'           => $cogs,
            'total_cogs'     => (float) $totalCogs,
            'gross_profit'   => (float) $grossProfit,
            'expenses'       => $expenses,
            'total_expenses' => (float) $totalExpenses,
            'net_profit'     => (float) $netProfit
        ];
    }

    /**
     * Generates Balance Sheet (Neraca Keuangan)
     */
    public static function getBalanceSheet(?int $branchId = null, ?string $asOfDate = null): array {
        $asOfDate = $asOfDate ?: date('Y-m-d');
        $where = "j.journal_date <= :as_of AND j.status = 'posted'";
        $params = ['as_of' => $asOfDate];

        if ($branchId !== null && $branchId > 0) {
            $where .= " AND j.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        // Assets
        $sqlAssets = "SELECT a.id, a.code, a.name, a.normal_balance,
                             SUM(CASE WHEN a.normal_balance = 'debit' THEN jd.debit - jd.credit ELSE jd.credit - jd.debit END) as balance
                      FROM accounts a
                      JOIN journal_details jd ON a.id = jd.account_id
                      JOIN journals j ON jd.journal_id = j.id
                      WHERE a.type = 'asset' AND {$where}
                      GROUP BY a.id, a.code, a.name, a.normal_balance
                      ORDER BY a.code ASC";
        $assets = Database::fetchAll($sqlAssets, $params);
        $totalAssets = array_sum(array_column($assets, 'balance'));

        // Liabilities
        $sqlLiab = "SELECT a.id, a.code, a.name, a.normal_balance,
                           SUM(CASE WHEN a.normal_balance = 'credit' THEN jd.credit - jd.debit ELSE jd.debit - jd.credit END) as balance
                    FROM accounts a
                    JOIN journal_details jd ON a.id = jd.account_id
                    JOIN journals j ON jd.journal_id = j.id
                    WHERE a.type = 'liability' AND {$where}
                    GROUP BY a.id, a.code, a.name, a.normal_balance
                    ORDER BY a.code ASC";
        $liabilities = Database::fetchAll($sqlLiab, $params);
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));

        // Equity
        $sqlEq = "SELECT a.id, a.code, a.name, a.normal_balance,
                         SUM(CASE WHEN a.normal_balance = 'credit' THEN jd.credit - jd.debit ELSE jd.debit - jd.credit END) as balance
                  FROM accounts a
                  JOIN journal_details jd ON a.id = jd.account_id
                  JOIN journals j ON jd.journal_id = j.id
                  WHERE a.type = 'equity' AND {$where}
                  GROUP BY a.id, a.code, a.name, a.normal_balance
                  ORDER BY a.code ASC";
        $equity = Database::fetchAll($sqlEq, $params);
        $totalEquity = array_sum(array_column($equity, 'balance'));

        return [
            'as_of_date'        => $asOfDate,
            'branch_id'         => $branchId,
            'assets'            => $assets,
            'total_assets'      => (float) $totalAssets,
            'liabilities'       => $liabilities,
            'total_liabilities' => (float) $totalLiabilities,
            'equity'            => $equity,
            'total_equity'      => (float) $totalEquity
        ];
    }
}
