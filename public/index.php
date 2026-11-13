<?php
/**
 * PRINTMASTER - ERP Jasa Percetakan Multi-Cabang
 * Entry Point (Front Controller)
 */

declare(strict_types=1);

// Built-in PHP CLI Web Server static file router check
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $uri;
    if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
        return false;
    }
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize Session
\App\Core\Session::start();

// Load Web Routes
require_once dirname(__DIR__) . '/routes/web.php';

// Dispatch Request
\App\Core\Router::dispatch();
