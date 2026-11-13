<?php
namespace App\Core;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?array {
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function all(string $orderBy = 'id DESC'): array {
        $sql = "SELECT * FROM " . static::$table . " ORDER BY {$orderBy}";
        return Database::fetchAll($sql);
    }

    public static function where(string $where, array $params = [], string $orderBy = 'id DESC', ?int $limit = null): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE {$where} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }
        return Database::fetchAll($sql, $params);
    }

    public static function firstWhere(string $where, array $params = []): ?array {
        $sql = "SELECT * FROM " . static::$table . " WHERE {$where} LIMIT 1";
        return Database::fetchOne($sql, $params);
    }

    public static function count(string $where = '1=1', array $params = []): int {
        $sql = "SELECT COUNT(*) FROM " . static::$table . " WHERE {$where}";
        return (int) Database::fetchColumn($sql, $params);
    }

    public static function create(array $data): int {
        return Database::insert(static::$table, $data);
    }

    public static function updateById(int $id, array $data): int {
        return Database::update(static::$table, $data, static::$primaryKey . " = :pk_id", ['pk_id' => $id]);
    }

    public static function deleteById(int $id): int {
        return Database::delete(static::$table, static::$primaryKey . " = :pk_id", ['pk_id' => $id]);
    }
}
