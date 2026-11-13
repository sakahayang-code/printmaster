<?php
namespace App\Modules\Reports;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\FormatHelper;

class ReportController extends Controller {

    public function index() {
        Auth::requirePermission('reports.view');
        $branchId = Auth::activeBranchId();

        $startDate = Request::input('start_date', date('Y-m-01'));
        $endDate = Request::input('end_date', date('Y-m-d'));

        $where = "o.order_date BETWEEN :start AND :end AND o.status != 'cancelled'";
        $params = ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59'];

        if ($branchId) {
            $where .= " AND o.branch_id = :bid";
            $params['bid'] = $branchId;
        }

        // Summary KPI
        $summary = Database::fetch("
            SELECT COUNT(*) as total_orders,
                   COALESCE(SUM(o.total_amount), 0) as total_omset,
                   COALESCE(SUM(o.paid_amount), 0) as total_paid,
                   COALESCE(SUM(o.total_amount - o.paid_amount), 0) as total_piutang
            FROM orders o
            WHERE {$where}
        ", $params);

        // Sales by Branch (Perbandingan Cabang)
        $branchSales = Database::fetchAll("
            SELECT b.id, b.name as branch_name, b.code as branch_code,
                   COUNT(o.id) as order_count,
                   COALESCE(SUM(o.total_amount), 0) as omset
            FROM branches b
            LEFT JOIN orders o ON b.id = o.branch_id AND o.order_date BETWEEN :start AND :end AND o.status != 'cancelled'
            WHERE b.is_active = 1
            GROUP BY b.id, b.name, b.code
            ORDER BY omset DESC
        ", ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']);

        // Sales by Product Category
        $categorySales = Database::fetchAll("
            SELECT c.name as category_name,
                   COUNT(oi.id) as item_count,
                   COALESCE(SUM(oi.subtotal), 0) as total_sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            JOIN product_categories c ON p.category_id = c.id
            WHERE {$where}
            GROUP BY c.id, c.name
            ORDER BY total_sales DESC
        ", $params);

        // Top 5 Customers by Sales
        $topCustomers = Database::fetchAll("
            SELECT c.name, c.phone, c.customer_tier as tier_name,
                   COUNT(o.id) as order_count,
                   COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM customers c
            JOIN orders o ON c.id = o.customer_id
            WHERE {$where}
            GROUP BY c.id, c.name, c.phone, c.customer_tier
            ORDER BY total_spent DESC LIMIT 5
        ", $params);

        $this->view('reports/index', [
            'title'         => 'Laporan Manajemen & Analitik Penjualan',
            'summary'       => $summary,
            'branchSales'   => $branchSales,
            'categorySales' => $categorySales,
            'topCustomers'  => $topCustomers,
            'startDate'     => $startDate,
            'endDate'       => $endDate
        ]);
    }

    public function exportCsv() {
        Auth::requirePermission('reports.view');
        $branchId = Auth::activeBranchId();
        $startDate = Request::input('start_date', date('Y-m-01'));
        $endDate = Request::input('end_date', date('Y-m-d'));

        $where = "o.order_date BETWEEN :start AND :end";
        $params = ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59'];

        if ($branchId) {
            $where .= " AND o.branch_id = :bid";
            $params['bid'] = $branchId;
        }

        $orders = Database::fetchAll("
            SELECT o.order_number, o.order_date, b.name as branch_name, c.name as customer_name,
                   o.total_amount, o.paid_amount, (o.total_amount - o.paid_amount) as piutang,
                   o.status, o.payment_status
            FROM orders o
            JOIN branches b ON o.branch_id = b.id
            JOIN customers c ON o.customer_id = c.id
            WHERE {$where}
            ORDER BY o.id DESC
        ", $params);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_penjualan_' . $startDate . '_' . $endDate . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['No Pesanan', 'Tanggal', 'Cabang', 'Pelanggan', 'Total Tagihan', 'Dibayar', 'Sisa Piutang', 'Status Produksi', 'Status Bayar']);

        foreach ($orders as $row) {
            fputcsv($out, [
                $row['order_number'],
                $row['order_date'],
                $row['branch_name'],
                $row['customer_name'],
                $row['total_amount'],
                $row['paid_amount'],
                $row['piutang'],
                $row['status'],
                $row['payment_status']
            ]);
        }

        fclose($out);
        exit;
    }
}
