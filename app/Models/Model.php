<?php

namespace App\Models;

use App\Core\Application;
use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected ?PDO $db = null;

    public function __construct()
    {
        $this->db = Application::getInstance()->db();
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function all(array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $stmt = $this->db->query("SELECT $columnsStr FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(mixed $id, array $columns = ['*']): ?array
    {
        $columnsStr = implode(', ', $columns);
        $stmt = $this->db->prepare("SELECT $columnsStr FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findBy(string $column, mixed $value, array $columns = ['*']): ?array
    {
        $columnsStr = implode(', ', $columns);
        $stmt = $this->db->prepare("SELECT $columnsStr FROM {$this->table} WHERE $column = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function where(array $conditions, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $where = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $where[] = "$col = ?";
            $params[] = $val;
        }
        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT $columnsStr FROM {$this->table} WHERE $whereStr");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function firstWhere(array $conditions, array $columns = ['*']): ?array
    {
        $results = $this->where($conditions, $columns);
        return $results[0] ?? null;
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(mixed $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            return false;
        }
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "$col = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(mixed $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function paginate(int $page = 1, int $perPage = 15, array $conditions = [], array $columns = ['*']): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $col => $val) {
                $whereParts[] = "$col = ?";
                $params[] = $val;
            }
            $where = 'WHERE ' . implode(' AND ', $whereParts);
        }

        $columnsStr = implode(', ', $columns);
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $perPage;
        $params[] = $offset;
        $stmt = $this->db->prepare("SELECT $columnsStr FROM {$this->table} $where LIMIT ? OFFSET ?");
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $callback($this);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function applyCasts(array $data): array
    {
        foreach ($this->casts as $key => $type) {
            if (!isset($data[$key])) continue;
            $data[$key] = match ($type) {
                'int', 'integer' => (int)$data[$key],
                'float', 'double' => (float)$data[$key],
                'bool', 'boolean' => (bool)$data[$key],
                'array', 'json' => json_decode($data[$key], true),
                'object' => json_decode($data[$key]),
                'date' => $data[$key] instanceof \DateTime ? $data[$key]->format('Y-m-d') : $data[$key],
                'datetime' => $data[$key] instanceof \DateTime ? $data[$key]->format('Y-m-d H:i:s') : $data[$key],
                default => $data[$key],
            };
        }
        return $data;
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->fillable as $field) {
            $data[$field] = $this->$field ?? null;
        }
        return $this->applyCasts($data);
    }

    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }
}