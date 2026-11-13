<?php
namespace App\Modules\Dashboard;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $activeBranchId = Auth::activeBranchId();
        $isConsolidated = (Request::get('view') === 'all') && (Auth::isSuperAdmin() || Auth::isOwner());

        $orderBranchFilter = $isConsolidated ? "1=1" : "orders.branch_id = {$activeBranchId}";
        $oBranchFilter = $isConsolidated ? "1=1" : "o.branch_id = {$activeBranchId}";
        $jobBranchFilter = $isConsolidated ? "1=1" : "pj.target_branch_id = {$activeBranchId}";
        $currentMonth = date('Y-m');

        // 1. Total Omzet Bulan Ini
        $sqlOmzet = "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled' AND DATE_FORMAT(order_date, '%Y-%m') = :ym AND {$orderBranchFilter}";
        $omzetBulanIni = (float) Database::fetchColumn($sqlOmzet, ['ym' => $currentMonth]);

        // 2. Total Order Bulan Ini
        $sqlTotalOrder = "SELECT COUNT(*) FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = :ym AND {$orderBranchFilter}";
        $totalOrderBulanIni = (int) Database::fetchColumn($sqlTotalOrder, ['ym' => $currentMonth]);

        // 3. Order Dalam Produksi
        $sqlInProd = "SELECT COUNT(*) FROM orders WHERE status IN ('queued', 'in_production', 'finishing', 'quality_control') AND {$orderBranchFilter}";
        $inProductionCount = (int) Database::fetchColumn($sqlInProd);

        // 4. Total Piutang Belum Tertagih
        $sqlPiutang = "SELECT COALESCE(SUM(remaining_amount), 0) FROM orders WHERE payment_status IN ('unpaid', 'partial') AND status != 'cancelled' AND {$orderBranchFilter}";
        $totalPiutang = (float) Database::fetchColumn($sqlPiutang);

        // 5. Bahan Baku Menipis (Stok <= min_stock)
        $lowStockMaterials = Database::fetchAll("SELECT * FROM materials WHERE current_stock <= min_stock AND is_active = 1 ORDER BY current_stock ASC LIMIT 5");

        // 6. Pesanan Terbaru
        $recentOrders = Database::fetchAll("
            SELECT o.*, c.name as customer_name, b.name as branch_name 
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN branches b ON o.branch_id = b.id
            WHERE {$oBranchFilter}
            ORDER BY o.id DESC LIMIT 8
        ");

        // 7. Antrean Produksi Terkini
        $recentJobs = Database::fetchAll("
            SELECT pj.*, m.name as machine_name, u.name as operator_name, o.order_number, oi.item_name
            FROM production_jobs pj
            JOIN orders o ON pj.order_id = o.id
            JOIN order_items oi ON pj.order_item_id = oi.id
            LEFT JOIN machines m ON pj.machine_id = m.id
            LEFT JOIN users u ON pj.operator_id = u.id
            WHERE pj.status IN ('queued', 'scheduled', 'in_progress') AND {$jobBranchFilter}
            ORDER BY pj.priority = 'urgent' DESC, pj.priority = 'high' DESC, pj.id ASC
            LIMIT 6
        ");

        // 8. Ringkasan Omzet Per Cabang (Khusus Owner / Super Admin)
        $branchSummaries = [];
        if (Auth::isSuperAdmin() || Auth::isOwner()) {
            $branchSummaries = Database::fetchAll("
                SELECT b.id, b.name, b.code, b.monthly_target,
                       COALESCE(SUM(o.total_amount), 0) as total_omzet,
                       COUNT(o.id) as total_orders
                FROM branches b
                LEFT JOIN orders o ON b.id = o.branch_id AND o.status != 'cancelled' AND DATE_FORMAT(o.order_date, '%Y-%m') = :ym
                WHERE b.is_active = 1
                GROUP BY b.id, b.name, b.code, b.monthly_target
                ORDER BY total_omzet DESC
            ", ['ym' => $currentMonth]);
        }

        // 9. Data Grafik Tren 7 Hari Terakhir
        $chartDates = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $dt = date('Y-m-d', strtotime("-{$i} days"));
            $chartDates[] = date('d M', strtotime($dt));
            $sqlD = "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_date = :dt AND status != 'cancelled' AND {$orderBranchFilter}";
            $chartSales[] = (float) Database::fetchColumn($sqlD, ['dt' => $dt]);
        }

        $this->render('dashboard/index', [
            'title'              => 'Dashboard Operasional',
            'isConsolidated'     => $isConsolidated,
            'omzetBulanIni'      => $omzetBulanIni,
            'totalOrderBulanIni' => $totalOrderBulanIni,
            'inProductionCount'  => $inProductionCount,
            'totalPiutang'       => $totalPiutang,
            'lowStockMaterials'  => $lowStockMaterials,
            'recentOrders'       => $recentOrders,
            'recentJobs'         => $recentJobs,
            'branchSummaries'    => $branchSummaries,
            'chartDates'         => $chartDates,
            'chartSales'         => $chartSales
        ]);
    }
}
