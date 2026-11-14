<?php
namespace App\Core;

class Response {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url): void {
        $base = Request::basePath();
        if ($base !== '' && strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $trimmedBase = trim($base, '/');
            if (!preg_match('#^/' . preg_quote($trimmedBase, '#') . '(/|$)#i', $url)) {
                $url = '/' . $trimmedBase . $url;
            }
        }
        header("Location: {$url}");
        exit;
    }

    public static function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? (Request::basePath() ?: '/');
        self::redirect($referer);
    }
}
