<?php
/**
 * Test Subfolder / Subdirectory Support (e.g. multimediatama.com/percetakan/)
 */

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once dirname(__DIR__) . '/app/Helpers/functions.php';

use App\Core\Request;
use App\Core\View;
use App\Core\Router;

echo "====================================================\n";
echo "   PRINTMASTER ERP - SUBFOLDER SUPPORT TEST SUITE   \n";
echo "====================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertCondition(bool $cond, string $message): void {
    global $testsPassed, $testsFailed;
    if ($cond) {
        echo "[PASS] " . $message . "\n";
        $testsPassed++;
    } else {
        echo "[FAIL] " . $message . "\n";
        $testsFailed++;
    }
}

// 1. Test Base Path Auto-detection
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/percetakan/public/index.php';
$_SERVER['REQUEST_URI'] = '/percetakan/';

Request::resetBasePath();
$detectedBase = Request::basePath();
assertCondition($detectedBase === '/percetakan', "Auto-detected basePath from /percetakan/public/index.php and /percetakan/ is '/percetakan' (got '{$detectedBase}')");

$detectedUri = Request::uri();
assertCondition($detectedUri === '/', "Request::uri() for '/percetakan/' returns '/' (got '{$detectedUri}')");

// 2. Test inner page URI
$_SERVER['REQUEST_URI'] = '/percetakan/dashboard';
$innerUri = Request::uri();
assertCondition($innerUri === '/dashboard', "Request::uri() for '/percetakan/dashboard' returns '/dashboard' (got '{$innerUri}')");

// 3. Test View::prefixUrls
$sampleHtml = <<<HTML
<html>
<head>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/printmaster.css">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <a href="/dashboard">Dashboard</a>
    <a href="/percetakan/orders">Already prefixed</a>
    <a href="/">Home</a>
    <form action="/login" method="POST"></form>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;

$prefixed = View::prefixUrls($sampleHtml, '/percetakan');
assertCondition(strpos($prefixed, 'href="/percetakan/assets/css/bootstrap.min.css"') !== false, "CSS link prefixed to /percetakan/assets/css/bootstrap.min.css");
assertCondition(strpos($prefixed, 'href="/percetakan/assets/css/printmaster.css"') !== false, "PrintMaster CSS link prefixed to /percetakan/assets/css/printmaster.css");
assertCondition(strpos($prefixed, 'src="/percetakan/assets/js/bootstrap.bundle.min.js"') !== false, "JS script prefixed to /percetakan/assets/js/bootstrap.bundle.min.js");
assertCondition(strpos($prefixed, 'href="/percetakan/dashboard"') !== false, "Nav link prefixed to /percetakan/dashboard");
assertCondition(strpos($prefixed, 'href="/percetakan/orders"') !== false && strpos($prefixed, '/percetakan/percetakan') === false, "Already-prefixed link not double-prefixed");
assertCondition(strpos($prefixed, 'action="/percetakan/login"') !== false, "Form action prefixed to /percetakan/login");

// 4. Test global helpers
assertCondition(url('/orders') === '/percetakan/orders', "url('/orders') returns '/percetakan/orders'");
assertCondition(asset('/assets/css/bootstrap.min.css') === '/percetakan/assets/css/bootstrap.min.css', "asset('/assets/css/bootstrap.min.css') returns '/percetakan/assets/css/bootstrap.min.css'");

// 5. Test Root Domain (basePath empty)
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
$_SERVER['REQUEST_URI'] = '/dashboard';
Request::resetBasePath();
$rootBase = Request::basePath();
assertCondition($rootBase === '', "Root domain basePath is empty '' (got '{$rootBase}')");
$rootUri = Request::uri();
assertCondition($rootUri === '/dashboard', "Root domain uri is '/dashboard' (got '{$rootUri}')");
$rootHtml = View::prefixUrls($sampleHtml, '');
assertCondition(strpos($rootHtml, 'href="/assets/css/bootstrap.min.css"') !== false, "Root domain HTML unchanged");

echo "\n====================================================\n";
echo "SUMMARY: {$testsPassed} PASSED, {$testsFailed} FAILED.\n";
echo "====================================================\n";

exit($testsFailed > 0 ? 1 : 0);
