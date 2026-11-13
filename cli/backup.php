<?php
/**
 * PRINTMASTER Automated Database Backup CLI
 * 
 * Skrip ini dapat dijalankan melalui cPanel Cron Job harian:
 * 0 2 * * * php -q /path/ke/printmaster/cli/backup.php >/dev/null 2>&1
 */

declare(strict_types=1);

// Pastikan berjalan hanya di mode CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip ini hanya dapat dijalankan melalui terminal CLI.\n");
}

require_once dirname(__DIR__) . '/app/Config/Database.php';

echo "====================================================\n";
echo "   PRINTMASTER AUTOMATED DATABASE BACKUP WORKER     \n";
echo "====================================================\n";
echo "Waktu mulai: " . date('Y-m-d H:i:s') . "\n";

$dbConfig = \App\Config\Database::getConfig();
$socketPart = file_exists('/tmp/mysql.sock') ? ";unix_socket=/tmp/mysql.sock" : "";
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}{$socketPart}";

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
} catch (Exception $e) {
    die("ERROR: Gagal terhubung ke database: " . $e->getMessage() . "\n");
}

$backupDir = dirname(__DIR__) . '/storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$timestamp = date('Ymd_His');
$filename = "printmaster_auto_{$timestamp}.sql";
$filePath = $backupDir . '/' . $filename;

echo "Mencadangkan " . count($tables) . " tabel...\n";

$sqlDump = "-- ====================================================\n";
$sqlDump .= "-- PRINTMASTER AUTOMATED BACKUP DUMP\n";
$sqlDump .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
$sqlDump .= "-- Versi Database: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
$sqlDump .= "-- Catatan: Dibuat aman tanpa DROP TABLE destruktif\n";
$sqlDump .= "-- ====================================================\n\n";
$sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sqlDump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

foreach ($tables as $table) {
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
    $createTableSql = $createStmt['Create Table'] ?? '';
    // Pastikan CREATE TABLE IF NOT EXISTS
    $createTableSql = preg_replace('/CREATE TABLE\s+(?!IF NOT EXISTS)/i', 'CREATE TABLE IF NOT EXISTS ', $createTableSql);

    $sqlDump .= "-- Struktur tabel: `{$table}`\n";
    $sqlDump .= $createTableSql . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $sqlDump .= "INSERT IGNORE INTO `{$table}` VALUES\n";
        $rowStrings = [];
        foreach ($rows as $row) {
            $vals = array_map(function ($v) {
                return $v === null ? "NULL" : "'" . addslashes((string)$v) . "'";
            }, array_values($row));
            $rowStrings[] = "(" . implode(", ", $vals) . ")";
        }
        $sqlDump .= implode(",\n", $rowStrings) . ";\n\n";
    }
}

$sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents($filePath, $sqlDump);
$fileSizeMb = round(filesize($filePath) / 1024 / 1024, 2);

echo "SUCCESS: Cadangan berhasil disimpan di: {$filePath} ({$fileSizeMb} MB)\n";

// Pembersihan otomatis berkas cadangan lama (> 30 hari)
$retentionDays = 30;
$cutoffTime = time() - ($retentionDays * 86400);
$allBackups = glob($backupDir . '/*.sql');
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
