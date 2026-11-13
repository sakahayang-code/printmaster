<?php
use App\Core\Router;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;

use App\Modules\Auth\AuthController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\POS\POSController;
use App\Modules\Orders\OrderController;
use App\Modules\Pricing\PricingController;
use App\Modules\Customers\CustomerController;
use App\Modules\Products\ProductController;
use App\Modules\Design\DesignController;
use App\Modules\Production\ProductionController;
use App\Modules\QC\QCController;
use App\Modules\Machines\MachineController;
use App\Modules\Inventory\InventoryController;
use App\Modules\Purchasing\PurchasingController;
use App\Modules\Accounting\AccountingController;
use App\Modules\HR\HRController;
use App\Modules\Delivery\DeliveryController;
use App\Modules\CustomerPortal\CustomerPortalController;
use App\Modules\Marketing\MarketingController;
use App\Modules\Reports\ReportController;
use App\Modules\WhatsApp\WhatsAppController;
use App\Modules\Company\CompanyController;

// ==========================================
// 1. PUBLIC & AUTHENTICATION ROUTES
// ==========================================
Router::get('/', function() {
    Response::redirect('/dashboard');
});

Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::post('/logout', [AuthController::class, 'logout']);
Router::post('/switch-branch', [AuthController::class, 'switchBranch'], [AuthMiddleware::class]);

// Customer Portal (Public Facing)
Router::get('/customer-portal', [CustomerPortalController::class, 'index']);
Router::get('/customer-portal/track', [CustomerPortalController::class, 'track']);
Router::get('/customer-portal/order', [CustomerPortalController::class, 'orderForm']);
Router::post('/customer-portal/order/store', [CustomerPortalController::class, 'submitOrder']);

// ==========================================
// 2. DASHBOARD
// ==========================================
Router::get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

// ==========================================
// 3. POS (KASIR CEPAT)
// ==========================================
Router::get('/pos', [POSController::class, 'index'], [AuthMiddleware::class]);
Router::post('/pos/checkout', [POSController::class, 'checkout'], [AuthMiddleware::class]);
Router::get('/pos/receipt/{id}', [POSController::class, 'receipt'], [AuthMiddleware::class]);

// ==========================================
// 4. ORDERS & SALES
// ==========================================
Router::get('/orders', [OrderController::class, 'index'], [AuthMiddleware::class]);
Router::get('/orders/create', [OrderController::class, 'create'], [AuthMiddleware::class]);
Router::post('/orders/store', [OrderController::class, 'store'], [AuthMiddleware::class]);
Router::get('/orders/{id}', [OrderController::class, 'show'], [AuthMiddleware::class]);
Router::post('/orders/{id}/pay', [OrderController::class, 'pay'], [AuthMiddleware::class]);
Router::post('/orders/{id}/status', [OrderController::class, 'updateStatus'], [AuthMiddleware::class]);
Router::get('/orders/{id}/invoice', [OrderController::class, 'invoice'], [AuthMiddleware::class]);
Router::get('/orders/{id}/receipt', [OrderController::class, 'receipt'], [AuthMiddleware::class]);
Router::get('/orders/{id}/spk', [OrderController::class, 'workOrder'], [AuthMiddleware::class]);

// ==========================================
// 5. PRICING & QUOTES
// ==========================================
Router::get('/pricing', [PricingController::class, 'index'], [AuthMiddleware::class]);
Router::post('/pricing/calculate', [PricingController::class, 'calculateAjax'], [AuthMiddleware::class]);
Router::get('/quotes', [PricingController::class, 'quotes'], [AuthMiddleware::class]);
Router::get('/quotes/create', [PricingController::class, 'createQuote'], [AuthMiddleware::class]);
Router::post('/quotes/store', [PricingController::class, 'storeQuote'], [AuthMiddleware::class]);

// ==========================================
// 6. CUSTOMERS & CRM
// ==========================================
Router::get('/customers', [CustomerController::class, 'index'], [AuthMiddleware::class]);
Router::post('/customers/store', [CustomerController::class, 'store'], [AuthMiddleware::class]);
Router::get('/customers/{id}', [CustomerController::class, 'show'], [AuthMiddleware::class]);

// ==========================================
// 7. PRODUCTS & CATALOG
// ==========================================
Router::get('/products', [ProductController::class, 'index'], [AuthMiddleware::class]);
Router::post('/products/store', [ProductController::class, 'store'], [AuthMiddleware::class]);
Router::post('/products/{id}/update', [ProductController::class, 'update'], [AuthMiddleware::class]);

// ==========================================
// 8. DESIGN FILES & PROOFING
// ==========================================
Router::get('/design', [DesignController::class, 'index'], [AuthMiddleware::class]);
Router::post('/design/proof', [DesignController::class, 'updateProof'], [AuthMiddleware::class]);

// ==========================================
// 9. PRODUCTION & KANBAN
// ==========================================
Router::get('/production', [ProductionController::class, 'index'], [AuthMiddleware::class]);
Router::post('/production/{id}/status', [ProductionController::class, 'updateStatus'], [AuthMiddleware::class]);
Router::post('/production/{id}/delegate', [ProductionController::class, 'delegateBranch'], [AuthMiddleware::class]);

// ==========================================
// 10. QC & REPRINT
// ==========================================
Router::get('/qc', [QCController::class, 'index'], [AuthMiddleware::class]);
Router::post('/qc/inspect', [QCController::class, 'inspect'], [AuthMiddleware::class]);

// ==========================================
// 11. MACHINES & MAINTENANCE
// ==========================================
Router::get('/machines', [MachineController::class, 'index'], [AuthMiddleware::class]);
Router::post('/machines/store', [MachineController::class, 'store'], [AuthMiddleware::class]);
Router::post('/machines/log', [MachineController::class, 'logUsage'], [AuthMiddleware::class]);
Router::post('/machines/maintenance', [MachineController::class, 'scheduleMaintenance'], [AuthMiddleware::class]);

// ==========================================
// 12. INVENTORY & WAREHOUSES
// ==========================================
Router::get('/inventory', [InventoryController::class, 'index'], [AuthMiddleware::class]);
Router::get('/inventory/movements', [InventoryController::class, 'movements'], [AuthMiddleware::class]);
Router::get('/inventory/transfers', [InventoryController::class, 'transfers'], [AuthMiddleware::class]);
Router::post('/inventory/transfers/store', [InventoryController::class, 'storeTransfer'], [AuthMiddleware::class]);
Router::post('/inventory/transfers/{id}/receive', [InventoryController::class, 'receiveTransfer'], [AuthMiddleware::class]);

// ==========================================
// 13. PURCHASING & SUPPLIERS
// ==========================================
Router::get('/purchasing', [PurchasingController::class, 'index'], [AuthMiddleware::class]);
Router::post('/purchasing/store', [PurchasingController::class, 'store'], [AuthMiddleware::class]);
Router::post('/purchasing/{id}/receive', [PurchasingController::class, 'receiveGoods'], [AuthMiddleware::class]);
Router::get('/suppliers', [PurchasingController::class, 'suppliers'], [AuthMiddleware::class]);
Router::post('/suppliers/store', [PurchasingController::class, 'storeSupplier'], [AuthMiddleware::class]);

// ==========================================
// 14. FINANCE & ACCOUNTING
// ==========================================
Router::get('/cash-shifts', [AccountingController::class, 'cashShifts'], [AuthMiddleware::class]);
Router::post('/cash-shifts/open', [AccountingController::class, 'openShift'], [AuthMiddleware::class]);
Router::post('/cash-shifts/close', [AccountingController::class, 'closeShift'], [AuthMiddleware::class]);
Router::get('/payments', [AccountingController::class, 'payments'], [AuthMiddleware::class]);
Router::post('/payments/verify', [AccountingController::class, 'verifyPayment'], [AuthMiddleware::class]);
Router::get('/expenses', [AccountingController::class, 'expenses'], [AuthMiddleware::class]);
Router::post('/expenses/store', [AccountingController::class, 'storeExpense'], [AuthMiddleware::class]);
Router::get('/accounting/journals', [AccountingController::class, 'journals'], [AuthMiddleware::class]);
Router::post('/accounting/journals/store', [AccountingController::class, 'storeJournal'], [AuthMiddleware::class]);
Router::get('/accounting/reports', [AccountingController::class, 'reports'], [AuthMiddleware::class]);

// ==========================================
// 15. HR & PAYROLL
// ==========================================
Router::get('/employees', [HRController::class, 'employees'], [AuthMiddleware::class]);
Router::post('/employees/store', [HRController::class, 'storeEmployee'], [AuthMiddleware::class]);
Router::post('/employees/update', [HRController::class, 'updateEmployee'], [AuthMiddleware::class]);
Router::get('/attendance', [HRController::class, 'attendance'], [AuthMiddleware::class]);
Router::post('/attendance/check-in', [HRController::class, 'checkIn'], [AuthMiddleware::class]);
Router::post('/attendance/check-out', [HRController::class, 'checkOut'], [AuthMiddleware::class]);
Router::get('/payroll', [HRController::class, 'payroll'], [AuthMiddleware::class]);
Router::post('/payroll/generate', [HRController::class, 'generatePayroll'], [AuthMiddleware::class]);
Router::post('/payroll/pay', [HRController::class, 'payPayroll'], [AuthMiddleware::class]);

// ==========================================
// 16. DELIVERY & LOGISTICS
// ==========================================
Router::get('/delivery', [DeliveryController::class, 'index'], [AuthMiddleware::class]);
Router::post('/delivery/store', [DeliveryController::class, 'store'], [AuthMiddleware::class]);
Router::post('/delivery/status', [DeliveryController::class, 'updateStatus'], [AuthMiddleware::class]);
Router::get('/delivery/{id}/print', [DeliveryController::class, 'printSuratJalan'], [AuthMiddleware::class]);

// ==========================================
// 17. MARKETING & PROMOTIONS
// ==========================================
Router::get('/promotions', [MarketingController::class, 'index'], [AuthMiddleware::class]);
Router::post('/promotions/store', [MarketingController::class, 'store'], [AuthMiddleware::class]);
Router::get('/promotions/{id}/toggle', [MarketingController::class, 'toggle'], [AuthMiddleware::class]);

// ==========================================
// 18. REPORTS & ANALYTICS
// ==========================================
Router::get('/reports', [ReportController::class, 'index'], [AuthMiddleware::class]);
Router::get('/reports/export-csv', [ReportController::class, 'exportCsv'], [AuthMiddleware::class]);

// ==========================================
// 19. WHATSAPP GATEWAY SIMULATOR
// ==========================================
Router::get('/whatsapp', [WhatsAppController::class, 'index'], [AuthMiddleware::class]);
Router::post('/whatsapp/send-test', [WhatsAppController::class, 'sendTest'], [AuthMiddleware::class]);
Router::post('/whatsapp/template', [WhatsAppController::class, 'updateTemplate'], [AuthMiddleware::class]);

// ==========================================
// 20. COMPANY, USERS & AUDIT TRAIL
// ==========================================
Router::get('/branches', [CompanyController::class, 'branches'], [AuthMiddleware::class]);
Router::post('/branches/store', [CompanyController::class, 'storeBranch'], [AuthMiddleware::class]);
Router::post('/branches/{id}/toggle', [CompanyController::class, 'toggleBranch'], [AuthMiddleware::class]);
Router::get('/users', [CompanyController::class, 'users'], [AuthMiddleware::class]);
Router::post('/users/store', [CompanyController::class, 'storeUser'], [AuthMiddleware::class]);
Router::post('/users/{id}/toggle', [CompanyController::class, 'toggleUser'], [AuthMiddleware::class]);
Router::post('/users/{id}/reset-password', [CompanyController::class, 'resetPassword'], [AuthMiddleware::class]);
Router::get('/audit-logs', [CompanyController::class, 'auditLogs'], [AuthMiddleware::class]);
Router::get('/backup', [CompanyController::class, 'backupIndex'], [AuthMiddleware::class]);
Router::post('/backup/create', [CompanyController::class, 'createBackup'], [AuthMiddleware::class]);
Router::get('/backup/file/{file}', [CompanyController::class, 'downloadFile'], [AuthMiddleware::class]);
Router::post('/backup/delete', [CompanyController::class, 'deleteBackupFile'], [AuthMiddleware::class]);
Router::post('/backup/restore', [CompanyController::class, 'restoreBackup'], [AuthMiddleware::class]);
Router::post('/backup/migrate', [CompanyController::class, 'runMigrations'], [AuthMiddleware::class]);
Router::get('/backup/download', [CompanyController::class, 'downloadBackup'], [AuthMiddleware::class]);
