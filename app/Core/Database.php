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
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";
            try {
                self::$instance = new PDO($dsn, $c['username'], $c['password'], $c['options']);
            } catch (Exception $e) {
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
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
