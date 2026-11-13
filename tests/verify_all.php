<?php
/**
 * Comprehensive Automated Verification Suite for PRINTMASTER
 */

$baseUrl = 'http://127.0.0.1:8080';
$cookieFile = '/tmp/pm_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function httpReq($url, $method = 'GET', $data = [], $follow = true) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $res];
}

echo "====================================================\n";
echo "   PRINTMASTER ERP - AUTOMATED VERIFICATION SUITE   \n";
echo "====================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest($name, $condition, $info = '') {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] {$name}" . ($info ? " - {$info}" : "") . "\n";
        $testsPassed++;
    } else {
        echo "[FAIL] {$name}" . ($info ? " - {$info}" : "") . "\n";
        $testsFailed++;
    }
}

// 1. GET LOGIN PAGE & EXTRACT CSRF
$r = httpReq("{$baseUrl}/login");
assertTest("1. GET /login", $r['code'] === 200, "HTTP {$r['code']}");
preg_match('/name="_csrf_token" value="([^"]+)"/', $r['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';
assertTest("1b. CSRF Token extraction", !empty($csrfToken), substr($csrfToken, 0, 10) . '...');

// 2. AUTHENTICATE AS SUPER ADMIN
$loginRes = httpReq("{$baseUrl}/login", 'POST', [
    '_csrf_token' => $csrfToken,
    'username'    => 'admin',
    'password'    => 'password123'
]);
assertTest("2. POST /login (Super Admin)", $loginRes['code'] === 200 && strpos($loginRes['body'], 'Dashboard') !== false, "Super Admin logged in");

// 3. VERIFY ALL 21 ERP MODULE ENDPOINTS
$moduleEndpoints = [
    '/dashboard'            => 'Dashboard Manajemen',
    '/pos'                  => 'Kasir Cepat',
    '/orders'               => 'Daftar Pesanan',
    '/orders/create'        => 'Buat Pesanan Baru',
    '/pricing'              => 'Kalkulator Harga',
    '/quotes'               => 'Daftar Penawaran',
    '/quotes/create'        => 'Buat Penawaran',
    '/customers'            => 'Data Pelanggan & CRM',
    '/products'             => 'Katalog Produk',
    '/design'               => 'File Desain & Proofing',
    '/production'           => 'Manajemen Produksi',
    '/qc'                   => 'Quality Control',
    '/machines'             => 'Mesin & Pemeliharaan',
    '/inventory'            => 'Stok Gudang',
    '/inventory/movements'  => 'Kartu Stok',
    '/inventory/transfers'  => 'Transfer Stok Antar-Cabang',
    '/purchasing'           => 'Pembelian & PO',
    '/suppliers'            => 'Data Pemasok',
    '/cash-shifts'          => 'Shift Kasir',
    '/payments'             => 'Data Pembayaran',
    '/expenses'             => 'Biaya & Kas Keluar',
    '/accounting/journals'  => 'Buku Jurnal Umum',
    '/accounting/reports'   => 'Laba Rugi & Neraca',
    '/employees'            => 'Karyawan & SDM',
    '/attendance'           => 'Absensi & Kehadiran',
    '/payroll'              => 'Penggajian',
    '/delivery'             => 'Pengiriman & Surat Jalan',
    '/promotions'           => 'Voucher Promo',
    '/reports'              => 'Laporan Manajemen',
    '/whatsapp'             => 'WhatsApp Gateway Simulator',
    '/branches'             => 'Manajemen Cabang',
    '/users'                => 'Pengguna & Hak Akses',
    '/audit-logs'           => 'Audit Trail'
];

foreach ($moduleEndpoints as $path => $desc) {
    $res = httpReq("{$baseUrl}{$path}");
    assertTest("Module: {$path}", $res['code'] === 200, "{$desc} (HTTP 200)");
}

// 4. VERIFY CUSTOMER PORTAL (PUBLIC FACING)
$portalRes = httpReq("{$baseUrl}/customer-portal");
assertTest("Public: /customer-portal", $portalRes['code'] === 200 && strpos($portalRes['body'], 'PRINTMASTER') !== false, "Customer Portal loaded");

$trackRes = httpReq("{$baseUrl}/customer-portal/track?q=ORD-202611-0001");
assertTest("Public: /customer-portal/track", $trackRes['code'] === 200, "Tracking verified");

// 5. TEST CREATING ONLINE ORDER VIA PORTAL
$newOrderRes = httpReq("{$baseUrl}/customer-portal/order/store", 'POST', [
    '_csrf_token'      => $csrfToken,
    'branch_id'        => 1,
    'customer_name'    => 'Test Pelanggan Online',
    'customer_phone'   => '08999888777',
    'customer_email'   => 'test@portal.com',
    'customer_address' => 'Jl. Pengujian Online No. 123',
    'product_id'       => 1,
    'quantity'         => 2,
    'width'            => 100,
    'length'           => 200,
    'notes'            => 'https://drive.google.com/test-order'
]);
assertTest("Customer Portal: Online Order Submission", $newOrderRes['code'] === 200 && strpos($newOrderRes['body'], 'Status Pelacakan Pesanan') !== false, "Order auto-routed to tracking");

// 6. MULTI-BRANCH SWITCHING TEST
$switchRes = httpReq("{$baseUrl}/switch-branch", 'POST', [
    '_csrf_token' => $csrfToken,
    'branch_id'   => 2 // Surabaya
]);
assertTest("Multi-Branch: Switch to Surabaya (ID: 2)", $switchRes['code'] === 200, "Active branch switched");

// 7. EXPORT CSV TEST
$csvRes = httpReq("{$baseUrl}/reports/export-csv");
assertTest("Reports: CSV Export", $csvRes['code'] === 200 && strpos($csvRes['body'], 'No Pesanan') !== false && strpos($csvRes['body'], 'Cabang') !== false, "CSV header validated");

// 8. DATABASE BACKUP DUMP TEST
$backupRes = httpReq("{$baseUrl}/backup/download");
assertTest("Backup: SQL Export", $backupRes['code'] === 200 && strpos($backupRes['body'], 'PRINTMASTER DATABASE BACKUP') !== false, "SQL Backup generated");

echo "\n====================================================\n";
echo "SUMMARY: {$testsPassed} PASSED, {$testsFailed} FAILED.\n";
echo "====================================================\n";
