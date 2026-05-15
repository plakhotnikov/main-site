<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected static string $table = '';
    protected static array $fillable = [];
    protected static string $primaryKey = 'id';

    public static function find(int|string $id): ?array
    {
        return Database::one(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1',
            [$id]
        );
    }

    public static function all(array $where = [], string $order = ''): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        $params = [];
        if (!empty($where)) {
            $clauses = [];
            foreach ($where as $col => $val) {
                if ($val === null) {
                    $clauses[] = $col . ' IS NULL';
                } else {
                    $clauses[] = $col . ' = ?';
                    $params[] = $val;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }
        if ($order !== '') {
            $sql .= ' ORDER BY ' . $order;
        }
        return Database::query($sql, $params);
    }

    public static function create(array $data): int
    {
        $data = self::filterFillable($data);
        if (empty($data)) {
            throw new \RuntimeException('Нет данных для вставки в ' . static::$table);
        }
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(', ', $cols)
            . ') VALUES (' . implode(', ', $placeholders) . ')';
        Database::execute($sql, array_values($data));
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int|string $id, array $data): int
    {
        $data = self::filterFillable($data);
        if (empty($data)) {
            return 0;
        }
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            $set[] = $col . ' = ?';
            $params[] = $val;
        }
        $params[] = $id;
        $sql = 'UPDATE ' . static::$table . ' SET ' . implode(', ', $set)
            . ' WHERE ' . static::$primaryKey . ' = ?';
        return Database::execute($sql, $params);
    }

    public static function delete(int|string $id): int
    {
        return Database::execute(
            'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?',
            [$id]
        );
    }

    public static function bulkDelete(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_filter($ids, static fn ($v) => $v > 0);
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'DELETE FROM ' . static::$table
            . ' WHERE ' . static::$primaryKey . ' IN (' . $placeholders . ')';
        return Database::execute($sql, array_values($ids));
    }

    private static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }
}
