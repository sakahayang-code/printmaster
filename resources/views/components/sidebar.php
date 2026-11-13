<?php
use App\Core\Auth;
use App\Core\Request;

$uri = Request::uri();
function isActive($path, $current) {
    if ($path === '/' && $current === '/') return 'active';
    if ($path !== '/' && strpos($current, $path) === 0) return 'active';
    return '';
}
?>
<div id="sidebar-wrapper">
    <a href="/dashboard" class="sidebar-brand">
        <i class="bi bi-printer-fill text-primary fs-3"></i>
        <div>
            <div class="fw-bold tracking-tight">PRINTMASTER</div>
            <div class="text-xs text-muted fw-normal" style="font-size: 0.68rem; letter-spacing: 0.05em;">ERP PERCETAKAN MULTI-CABANG</div>
        </div>
    </a>

    <div class="py-2">
        <!-- SEKSI UTAMA -->
        <div class="sidebar-heading">Operasional Utama</div>
        <a href="/dashboard" class="nav-link-pm <?= isActive('/dashboard', $uri) ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <?php if (Auth::hasPermission('orders.create')): ?>
        <a href="/pos" class="nav-link-pm <?= isActive('/pos', $uri) ?>">
            <i class="bi bi-calculator"></i>
            <span class="d-flex justify-content-between align-items-center w-100">
                Kasir POS
                <span class="badge bg-primary rounded-pill">Kasir</span>
            </span>
        </a>
        <?php endif; ?>

        <!-- SEKSI PENJUALAN & CRM -->
        <div class="sidebar-heading">Penjualan & Pelanggan</div>
        <?php if (Auth::hasPermission('orders.view')): ?>
        <a href="/orders" class="nav-link-pm <?= isActive('/orders', $uri) ?>">
            <i class="bi bi-bag-check"></i>
            <span>Pesanan & Penjualan</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('pricing.calculate')): ?>
        <a href="/pricing" class="nav-link-pm <?= isActive('/pricing', $uri) ?>">
            <i class="bi bi-calculator-fill"></i>
            <span>Kalkulator Harga & HPP</span>
        </a>
        <a href="/quotes" class="nav-link-pm <?= isActive('/quotes', $uri) ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Penawaran (Quotes)</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('customers.view')): ?>
        <a href="/customers" class="nav-link-pm <?= isActive('/customers', $uri) ?>">
            <i class="bi bi-people"></i>
            <span>Pelanggan (CRM)</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('catalog.view')): ?>
        <a href="/products" class="nav-link-pm <?= isActive('/products', $uri) ?>">
            <i class="bi bi-grid"></i>
            <span>Katalog Produk & Harga</span>
        </a>
        <?php endif; ?>

        <!-- SEKSI DESAIN & PRODUKSI -->
        <div class="sidebar-heading">Desain & Produksi</div>
        <?php if (Auth::hasPermission('design.view')): ?>
        <a href="/designs" class="nav-link-pm <?= isActive('/designs', $uri) ?>">
            <i class="bi bi-image"></i>
            <span>File Desain & Proofing</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('production.view')): ?>
        <a href="/production" class="nav-link-pm <?= isActive('/production', $uri) ?>">
            <i class="bi bi-kanban"></i>
            <span>Antrean Produksi</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('qc.inspect')): ?>
        <a href="/qc" class="nav-link-pm <?= isActive('/qc', $uri) ?>">
            <i class="bi bi-shield-check"></i>
            <span>Quality Control (QC)</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('machines.view')): ?>
        <a href="/machines" class="nav-link-pm <?= isActive('/machines', $uri) ?>">
            <i class="bi bi-cpu"></i>
            <span>Mesin & Pemeliharaan</span>
        </a>
        <?php endif; ?>

        <!-- SEKSI GUDANG & PENGADAAN -->
        <div class="sidebar-heading">Gudang & Bahan Baku</div>
        <?php if (Auth::hasPermission('inventory.view')): ?>
        <a href="/inventory" class="nav-link-pm <?= isActive('/inventory', $uri) ?>">
            <i class="bi bi-box-seam"></i>
            <span>Stok Bahan & Gudang</span>
        </a>
        <a href="/inventory/transfers" class="nav-link-pm <?= isActive('/inventory/transfers', $uri) ?>">
            <i class="bi bi-arrow-left-right"></i>
            <span>Transfer Stok Cabang</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('purchasing.view')): ?>
        <a href="/purchasing" class="nav-link-pm <?= isActive('/purchasing', $uri) ?>">
            <i class="bi bi-cart-check"></i>
            <span>Pembelian & PO Supplier</span>
        </a>
        <a href="/suppliers" class="nav-link-pm <?= isActive('/suppliers', $uri) ?>">
            <i class="bi bi-truck-front"></i>
            <span>Data Pemasok (Supplier)</span>
        </a>
        <?php endif; ?>

        <!-- SEKSI KEUANGAN & AKUNTANSI -->
        <div class="sidebar-heading">Keuangan & Akuntansi</div>
        <?php if (Auth::hasPermission('cash_shift.manage')): ?>
        <a href="/cash-shifts" class="nav-link-pm <?= isActive('/cash-shifts', $uri) ?>">
            <i class="bi bi-clock-history"></i>
            <span>Shift Kasir & Tutup Buku</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('payments.view')): ?>
        <a href="/payments" class="nav-link-pm <?= isActive('/payments', $uri) ?>">
            <i class="bi bi-cash-stack"></i>
            <span>Pembayaran & Piutang</span>
        </a>
        <a href="/expenses" class="nav-link-pm <?= isActive('/expenses', $uri) ?>">
            <i class="bi bi-receipt"></i>
            <span>Biaya & Kas Keluar</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('accounting.view')): ?>
        <a href="/accounting/journals" class="nav-link-pm <?= isActive('/accounting/journals', $uri) ?>">
            <i class="bi bi-journal-bookmark"></i>
            <span>Jurnal Umum</span>
        </a>
        <a href="/accounting/reports" class="nav-link-pm <?= isActive('/accounting/reports', $uri) ?>">
            <i class="bi bi-graph-up"></i>
            <span>Laba Rugi & Neraca</span>
        </a>
        <?php endif; ?>

        <!-- SEKSI SDM & PENGIRIMAN -->
        <div class="sidebar-heading">SDM & Logistik</div>
        <?php if (Auth::hasPermission('hr.employees')): ?>
        <a href="/employees" class="nav-link-pm <?= isActive('/employees', $uri) ?>">
            <i class="bi bi-person-badge"></i>
            <span>Karyawan & SDM</span>
        </a>
        <a href="/attendance" class="nav-link-pm <?= isActive('/attendance', $uri) ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Absensi & Kehadiran</span>
        </a>
        <a href="/payroll" class="nav-link-pm <?= isActive('/payroll', $uri) ?>">
            <i class="bi bi-wallet"></i>
            <span>Penggajian (Payroll)</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('delivery.view')): ?>
        <a href="/delivery" class="nav-link-pm <?= isActive('/delivery', $uri) ?>">
            <i class="bi bi-truck"></i>
            <span>Pengiriman & Kurir</span>
        </a>
        <?php endif; ?>

        <!-- SEKSI LAPORAN, MARKETING & SISTEM -->
        <div class="sidebar-heading">Laporan & Pengaturan</div>
        <?php if (Auth::hasPermission('marketing.manage')): ?>
        <a href="/promotions" class="nav-link-pm <?= isActive('/promotions', $uri) ?>">
            <i class="bi bi-tag"></i>
            <span>Voucher & Promo</span>
        </a>
        <?php endif; ?>
        <a href="/customer-portal" class="nav-link-pm <?= isActive('/customer-portal', $uri) ?>" target="_blank">
            <i class="bi bi-box-arrow-up-right text-info"></i>
            <span>Portal Pelanggan <i class="bi bi-arrow-up-right small"></i></span>
        </a>
        <?php if (Auth::hasPermission('reports.view')): ?>
        <a href="/reports" class="nav-link-pm <?= isActive('/reports', $uri) ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Laporan Manajemen</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('whatsapp.manage')): ?>
        <a href="/whatsapp" class="nav-link-pm <?= isActive('/whatsapp', $uri) ?>">
            <i class="bi bi-whatsapp text-success"></i>
            <span>Notifikasi WhatsApp</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('company.manage')): ?>
        <a href="/branches" class="nav-link-pm <?= isActive('/branches', $uri) ?>">
            <i class="bi bi-buildings"></i>
            <span>Manajemen Cabang</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('users.manage')): ?>
        <a href="/users" class="nav-link-pm <?= isActive('/users', $uri) ?>">
            <i class="bi bi-shield-lock"></i>
            <span>Pengguna & Hak Akses</span>
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('audit.view')): ?>
        <a href="/audit-logs" class="nav-link-pm <?= isActive('/audit-logs', $uri) ?>">
            <i class="bi bi-activity"></i>
            <span>Audit Trail & Backup</span>
        </a>
        <?php endif; ?>
    </div>
</div>
