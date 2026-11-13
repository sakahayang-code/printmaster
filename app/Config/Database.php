<?php
namespace App\Config;

class Database {
    public static function getConfig(): array {
        $config = [
            'host'     => getenv('DB_HOST') ?: '127.0.0.1',
            'port'     => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_NAME') ?: 'printmaster',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset'  => 'utf8mb4',
            'options'  => [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        ];

        // 1. Check for root .env file
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\n\r\0\x0B\"'");
                    if ($k === 'DB_HOST') $config['host'] = $v;
                    if ($k === 'DB_PORT') $config['port'] = $v;
                    if ($k === 'DB_NAME') $config['database'] = $v;
                    if ($k === 'DB_USER') $config['username'] = $v;
                    if ($k === 'DB_PASS') $config['password'] = $v;
                }
            }
        }

        // 2. Check for config/database.local.php (highest precedence, never overwritten by git)
        $localConfig = dirname(__DIR__, 2) . '/config/database.local.php';
        if (file_exists($localConfig)) {
            $overrides = require $localConfig;
            if (is_array($overrides)) {
                $config = array_merge($config, $overrides);
            }
        }

        return $config;
    }
}
