<?php
namespace App\Core;

class Request {
    private static ?string $basePath = null;

    public static function resetBasePath(): void {
        self::$basePath = null;
    }

    public static function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool {
        return self::method() === 'POST';
    }

    public static function isGet(): bool {
        return self::method() === 'GET';
    }

    public static function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public static function basePath(): string {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        // 1. Explicit override from environment or server
        $envBase = getenv('APP_BASE_PATH') ?: ($_ENV['APP_BASE_PATH'] ?? ($_SERVER['APP_BASE_PATH'] ?? null));
        if ($envBase !== null && $envBase !== false && $envBase !== '') {
            self::$basePath = '/' . trim((string)$envBase, '/');
            return self::$basePath;
        }

        // 2. Auto-detection from SCRIPT_NAME and REQUEST_URI
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = dirname($scriptName);
        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\') {
            $scriptDir = '';
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Check if path directly starts with scriptDir (e.g. /percetakan/public or /percetakan)
        if ($scriptDir !== '' && strpos($path, $scriptDir) === 0) {
            self::$basePath = rtrim($scriptDir, '/');
            return self::$basePath;
        }

        // Check if scriptDir ends with /public, but user accessed without /public (e.g. /percetakan/)
        // via root .htaccess rewrite
        if (preg_match('#^(.+)/public$#i', $scriptDir, $m)) {
            $parentDir = $m[1];
            if ($parentDir !== '' && strpos($path, $parentDir) === 0) {
                self::$basePath = rtrim($parentDir, '/');
                return self::$basePath;
            }
        }

        // Check CONTEXT_PREFIX (e.g. Apache Alias or userdir)
        if (!empty($_SERVER['CONTEXT_PREFIX'])) {
            self::$basePath = rtrim(str_replace('\\', '/', $_SERVER['CONTEXT_PREFIX']), '/');
            return self::$basePath;
        }

        self::$basePath = '';
        return self::$basePath;
    }

    public static function uri(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        $uri = rawurldecode($uri);

        $basePath = self::basePath();
        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Handle edge case where /public is left in URI
        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, 7);
        }

        return '/' . trim($uri, '/');
    }

    public static function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public static function input(string $key, $default = null) {
        if (isset($_POST[$key])) return $_POST[$key];
        if (isset($_GET[$key])) return $_GET[$key];
        $json = self::json();
        return $json[$key] ?? $default;
    }

    public static function all(): array {
        return array_merge($_GET, $_POST, self::json());
    }

    public static function json(): array {
        $input = file_get_contents('php://input');
        if ($input) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    public static function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    public static function ip(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
