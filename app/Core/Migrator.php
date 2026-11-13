<?php
namespace App\Core;

/**
 * PRINTMASTER Database Migrator
 * 
 * Menangani pembaruan struktur basis data secara aman tanpa menghapus tabel
 * atau data transaksi yang sudah ada.
 */
class Migrator {
    private static function initTable(): void {
        $idCol = Database::isSqlite() ? "id INTEGER PRIMARY KEY AUTOINCREMENT" : "id INT AUTO_INCREMENT PRIMARY KEY";
        $engine = Database::isSqlite() ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        Database::query("
            CREATE TABLE IF NOT EXISTS migrations (
                {$idCol},
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) {$engine}
        ");
    }

    public static function getExecutedMigrations(): array {
        self::initTable();
        $rows = Database::fetchAll("SELECT migration FROM migrations ORDER BY id ASC");
        return array_column($rows, 'migration');
    }

    public static function getAvailableMigrations(): array {
        $dir = dirname(__DIR__, 2) . '/database/migrations';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $files = scandir($dir);
        $migrations = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrations[] = $file;
            }
        }
        sort($migrations);
        return $migrations;
    }

    public static function getPendingMigrations(): array {
        $executed = self::getExecutedMigrations();
        $available = self::getAvailableMigrations();
        return array_values(array_diff($available, $executed));
    }

    public static function run(): array {
        self::initTable();
        $pending = self::getPendingMigrations();
        if (empty($pending)) {
            return ['status' => 'empty', 'message' => 'Tidak ada migrasi baru. Skema database sudah mutakhir.', 'migrated' => []];
        }

        $lastBatch = (int) Database::fetchColumn("SELECT COALESCE(MAX(batch), 0) FROM migrations");
        $currentBatch = $lastBatch + 1;
        $dir = dirname(__DIR__, 2) . '/database/migrations';
        $migrated = [];

        // Safety backup before applying migrations
        if (Database::isSqlite()) {
            $dbConfig = \App\Config\Database::getConfig();
            $dbPath = $dbConfig['database'];
            if (file_exists($dbPath)) {
                $backupDir = dirname(__DIR__, 2) . '/storage/backups';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                $backupFile = $backupDir . '/printmaster_pre_migration_' . date('Ymd_His') . '.sqlite';
                copy($dbPath, $backupFile);
            }
        }

        foreach ($pending as $file) {
            $path = $dir . '/' . $file;
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            try {
                if ($ext === 'sql') {
                    $sql = file_get_contents($path);
                    if (!empty(trim($sql))) {
                        Database::getPdo()->exec($sql);
                    }
                } elseif ($ext === 'php') {
                    $migrationClass = require_once $path;
                    if (is_object($migrationClass) && method_exists($migrationClass, 'up')) {
                        $migrationClass->up();
                    }
                }

                Database::insert('migrations', [
                    'migration' => $file,
                    'batch'     => $currentBatch
                ]);

                $migrated[] = $file;
            } catch (\Exception $e) {
                return [
                    'status'   => 'error',
                    'message'  => "Gagal menjalankan migrasi {$file}: " . $e->getMessage(),
                    'migrated' => $migrated
                ];
            }
        }

        return [
            'status'   => 'success',
            'message'  => 'Berhasil menjalankan ' . count($migrated) . ' migrasi database.',
            'migrated' => $migrated
        ];
    }
}
