<?php
/**
 * PRINTMASTER Automated Database Backup CLI
 * 
 * Skrip ini dapat dijalankan melalui cPanel Cron Job harian:
 * 0 2 * * * php -q /path/ke/printmaster/cli/backup.php >/dev/null 2>&1
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip ini hanya dapat dijalankan melalui terminal CLI.\n");
}

require_once dirname(__DIR__) . '/app/Config/Database.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

echo "====================================================\n";
echo "   PRINTMASTER AUTOMATED DATABASE BACKUP WORKER     \n";
echo "====================================================\n";
echo "Waktu mulai: " . date('Y-m-d H:i:s') . "\n";

try {
    $pdo = \App\Core\Database::getConnection();
} catch (Exception $e) {
    die("ERROR: Gagal terhubung ke database: " . $e->getMessage() . "\n");
}

$backupDir = dirname(__DIR__) . '/storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$dbConfig = \App\Config\Database::getConfig();
$isSqlite = \App\Core\Database::isSqlite();
$timestamp = date('Ymd_His');

if ($isSqlite) {
    $dbFile = $dbConfig['database'];
    $sqliteBackupName = "printmaster_auto_{$timestamp}.sqlite";
    $sqliteBackupPath = $backupDir . '/' . $sqliteBackupName;
    
    echo "Engine: SQLite ({$dbFile})\n";
    if (file_exists($dbFile)) {
        copy($dbFile, $sqliteBackupPath);
        $sizeMb = round(filesize($sqliteBackupPath) / 1024 / 1024, 2);
        echo "SUCCESS: Snapshot SQLite disimpan di: {$sqliteBackupPath} ({$sizeMb} MB)\n";
    }
}

// Juga buat berkas dump SQL standar
$tables = \App\Core\Database::fetchAll("SHOW TABLES");
$sqlBackupName = "printmaster_auto_{$timestamp}.sql";
$sqlBackupPath = $backupDir . '/' . $sqlBackupName;

echo "Mencadangkan " . count($tables) . " tabel ke format SQL...\n";

$sqlDump = "-- ====================================================\n";
$sqlDump .= "-- PRINTMASTER AUTOMATED BACKUP DUMP\n";
$sqlDump .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
$sqlDump .= "-- Engine: " . ($isSqlite ? 'SQLite' : 'MySQL/MariaDB') . "\n";
$sqlDump .= "-- ====================================================\n\n";

if (!$isSqlite) {
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sqlDump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";
} else {
    $sqlDump .= "PRAGMA foreign_keys = OFF;\n\n";
}

foreach ($tables as $tRow) {
    $table = $tRow['name'] ?? $tRow['Tables_in_printmaster'] ?? reset($tRow);
    $createStmt = \App\Core\Database::fetch("SHOW CREATE TABLE `{$table}`");
    $createTableSql = $createStmt['Create Table'] ?? '';
    $createTableSql = preg_replace('/CREATE TABLE\s+(?!IF NOT EXISTS)/i', 'CREATE TABLE IF NOT EXISTS ', $createTableSql);

    $sqlDump .= "-- Struktur tabel: `{$table}`\n";
    $sqlDump .= $createTableSql . ";\n\n";

    $rows = \App\Core\Database::fetchAll("SELECT * FROM `{$table}`");
    if (!empty($rows)) {
        $sqlDump .= ($isSqlite ? "INSERT OR IGNORE INTO `{$table}` VALUES\n" : "INSERT IGNORE INTO `{$table}` VALUES\n");
        $rowStrings = [];
        foreach ($rows as $row) {
            $vals = array_map(function ($v) use ($pdo) {
                return $v === null ? "NULL" : $pdo->quote((string)$v);
            }, array_values($row));
            $rowStrings[] = "(" . implode(", ", $vals) . ")";
        }
        $sqlDump .= implode(",\n", $rowStrings) . ";\n\n";
    }
}

if (!$isSqlite) {
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
} else {
    $sqlDump .= "PRAGMA foreign_keys = ON;\n";
}

file_put_contents($sqlBackupPath, $sqlDump);
$fileSizeMb = round(filesize($sqlBackupPath) / 1024 / 1024, 2);
echo "SUCCESS: Cadangan SQL berhasil disimpan di: {$sqlBackupPath} ({$fileSizeMb} MB)\n";

// Pembersihan otomatis berkas cadangan lama (> 30 hari)
$retentionDays = 30;
$cutoffTime = time() - ($retentionDays * 86400);
$allBackups = array_merge(glob($backupDir . '/*.sql'), glob($backupDir . '/*.sqlite'));
$prunedCount = 0;

foreach ($allBackups as $file) {
    if (filemtime($file) < $cutoffTime) {
        unlink($file);
        $prunedCount++;
    }
}

if ($prunedCount > 0) {
    echo "Pruned {$prunedCount} berkas cadangan lama (> 30 hari).\n";
}

echo "Selesai: " . date('Y-m-d H:i:s') . "\n";
echo "====================================================\n";
