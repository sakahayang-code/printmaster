<?php
namespace App\Core;

use App\Config\Database as DBConfig;
use PDO;
use Exception;

class Database {
    private static ?PDO $instance = null;
    private static int $transactionDepth = 0;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $c = DBConfig::getConfig();
            $driver = $c['driver'] ?? 'sqlite';

            if ($driver === 'sqlite') {
                $dbPath = $c['database'];
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $dsn = "sqlite:{$dbPath}";
                try {
                    self::$instance = new PDO($dsn, null, null, $c['options'] ?? [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                    // Enable WAL mode & foreign keys for high concurrency & integrity
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                    self::$instance->exec("PRAGMA journal_mode = WAL;");
                    self::$instance->exec("PRAGMA synchronous = NORMAL;");
                    self::$instance->exec("PRAGMA busy_timeout = 5000;");

                    // Register SQLite user-defined functions for full MySQL query compatibility
                    self::registerSqliteFunctions(self::$instance);

                    // Auto-initialize schema and seeds ONLY on a completely fresh, empty database (0 tables)
                    // If tables already exist, NO tables or data are ever modified or overwritten.
                    $tableCount = (int) self::$instance->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchColumn();
                    if ($tableCount === 0) {
                        self::autoInitializeSqlite(self::$instance);
                    }
                } catch (Exception $e) {
                    die("Koneksi Database SQLite Gagal: " . $e->getMessage());
                }
            } else {
                // MySQL / MariaDB connection
                $socketPart = (file_exists('/tmp/mysql.sock') && ($c['host'] === '127.0.0.1' || $c['host'] === 'localhost')) ? ";unix_socket=/tmp/mysql.sock" : "";
                $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}{$socketPart}";
                try {
                    self::$instance = new PDO($dsn, $c['username'], $c['password'], $c['options']);
                } catch (Exception $e) {
                    die("Koneksi Database Gagal: " . $e->getMessage());
                }
            }
        }
        return self::$instance;
    }

    public static function getPdo(): PDO {
        return self::getConnection();
    }

    public static function isSqlite(): bool {
        $c = DBConfig::getConfig();
        return ($c['driver'] ?? 'sqlite') === 'sqlite';
    }

    private static function registerSqliteFunctions(PDO $pdo): void {
        $pdo->sqliteCreateFunction('NOW', function () {
            return date('Y-m-d H:i:s');
        });
        $pdo->sqliteCreateFunction('CURDATE', function () {
            return date('Y-m-d');
        });
        $pdo->sqliteCreateFunction('CONCAT', function (...$args) {
            return implode('', $args);
        });
        $pdo->sqliteCreateFunction('IF', function ($cond, $a, $b) {
            return $cond ? $a : $b;
        });
        $pdo->sqliteCreateFunction('YEAR', function ($date) {
            return $date ? date('Y', strtotime($date)) : null;
        });
        $pdo->sqliteCreateFunction('MONTH', function ($date) {
            return $date ? date('m', strtotime($date)) : null;
        });
        $pdo->sqliteCreateFunction('DAY', function ($date) {
            return $date ? date('d', strtotime($date)) : null;
        });
        $pdo->sqliteCreateFunction('DATEDIFF', function ($d1, $d2) {
            if (!$d1 || !$d2) return 0;
            return (int) round((strtotime($d1) - strtotime($d2)) / 86400);
        });
        $pdo->sqliteCreateFunction('FIND_IN_SET', function ($str, $strlist) {
            if (!$strlist) return 0;
            $arr = explode(',', (string) $strlist);
            $idx = array_search((string) $str, $arr);
            return $idx === false ? 0 : $idx + 1;
        });
        $pdo->sqliteCreateFunction('DATE_FORMAT', function ($date, $format) {
            if (!$date) return null;
            $ts = strtotime((string)$date);
            if ($ts === false) return (string)$date;
            $replacements = [
                '%Y' => 'Y', '%y' => 'y', '%m' => 'm', '%c' => 'n',
                '%d' => 'd', '%e' => 'j', '%H' => 'H', '%h' => 'h',
                '%i' => 'i', '%s' => 's', '%W' => 'l', '%a' => 'D',
                '%b' => 'M', '%M' => 'F',
            ];
            $phpFormat = strtr($format, $replacements);
            return date($phpFormat, $ts);
        });
    }

    public static function autoInitializeSqlite(PDO $pdo): void {
        $schemaPath = dirname(__DIR__, 2) . '/database/printmaster.sqlite.sql';
        $seedPath   = dirname(__DIR__, 2) . '/database/seeds/initial_seeds.sql';

        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            if (!empty(trim($sql))) {
                $pdo->exec($sql);
            }
        }

        if (file_exists($seedPath)) {
            $seeds = file_get_contents($seedPath);
            $pwd = '$2y$10$4BSBcCyF9TOSFOn.GMBnGeBasaMVUqvc.Ajd.NerqQZlbofFb5RFy';
            $seeds = preg_replace('/SET\s+@pwd\s*=\s*[^;]+;/i', '', $seeds);
            $seeds = str_replace('@pwd', "'{$pwd}'", $seeds);
            $seeds = preg_replace('/INSERT\s+IGNORE\s+INTO/i', 'INSERT OR IGNORE INTO', $seeds);
            if (!empty(trim($seeds))) {
                $pdo->exec($seeds);
            }
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        if (self::isSqlite()) {
            $trimmed = trim($sql);
            if (strcasecmp($trimmed, 'SHOW TABLES') === 0) {
                $sql = "SELECT name as Tables_in_printmaster, name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC";
            } elseif (preg_match('/^SHOW\s+CREATE\s+TABLE\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $trimmed, $m)) {
                $tbl = $m[1];
                $sql = "SELECT sql as 'Create Table' FROM sqlite_master WHERE type='table' AND name = " . self::getConnection()->quote($tbl);
            } elseif (preg_match('/^(UPDATE|DELETE)\b/i', $trimmed) && preg_match('/\s+LIMIT\s+\d+\s*;?$/i', $trimmed)) {
                $sql = preg_replace('/\s+LIMIT\s+\d+\s*;?$/i', '', $trimmed);
            }
        }

        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        $res = self::query($sql, $params)->fetch();
        return $res ?: null;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        return self::fetchOne($sql, $params);
    }

    public static function fetchColumn(string $sql, array $params = []) {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function insert(string $table, array $data): int {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int) self::getConnection()->lastInsertId();
    }

    public static function lastInsertId(): int {
        return (int) self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $setParts = [];
        $params = [];
        foreach ($data as $key => $val) {
            $setParts[] = "{$key} = :set_{$key}";
            $params["set_{$key}"] = $val;
        }
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = self::query($sql, array_merge($params, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $whereParams = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool {
        if (self::$transactionDepth === 0) {
            self::getConnection()->beginTransaction();
        }
        self::$transactionDepth++;
        return true;
    }

    public static function commit(): bool {
        if (self::$transactionDepth > 0) {
            self::$transactionDepth--;
            if (self::$transactionDepth === 0) {
                return self::getConnection()->commit();
            }
        }
        return true;
    }

    public static function rollBack(): bool {
        if (self::$transactionDepth > 0) {
            self::$transactionDepth = 0;
            if (self::getConnection()->inTransaction()) {
                return self::getConnection()->rollBack();
            }
        }
        return true;
    }
}
