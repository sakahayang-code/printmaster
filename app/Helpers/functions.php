<?php
/**
 * PRINTMASTER - Global Helper Functions
 */

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        $base = \App\Core\Request::basePath();
        $path = '/' . ltrim($path, '/');
        if ($path === '/' && $base !== '') {
            return $base;
        }
        return $base . $path;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return base_path($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return base_path($path);
    }
}
