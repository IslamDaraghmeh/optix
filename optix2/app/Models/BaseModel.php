<?php
/**
 * Base Model Class
 *
 * Parent class for all models providing common database operations
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

use App\Helpers\Database;

abstract class BaseModel
{
    /**
     * @var Database Database instance
     */
    protected Database $db;

    /**
     * @var string Table name
     */
    protected string $table;

    /**
     * @var string Primary key column
     */
    protected string $primaryKey = 'id';

    /**
     * @var bool Use soft deletes
     */
    protected bool $useSoftDeletes = true;

    /**
     * @var bool Use timestamps
     */
    protected bool $useTimestamps = true;

    /**
     * @var string Created at column
     */
    protected string $createdAt = 'created_at';

    /**
     * @var string Updated at column
     */
    protected string $updatedAt = 'updated_at';

    /**
     * @var string Deleted at column
     */
    protected string $deletedAt = 'deleted_at';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find record by ID
     *
     * @param int $id Record ID
     * @param bool $withDeleted Include soft deleted records
     * @return array|false
     */
    public function find(int $id, bool $withDeleted = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";

        if ($this->useSoftDeletes && !$withDeleted) {
            $sql .= " AND {$this->deletedAt} IS NULL";
        }

        return $this->db->selectOne($sql, [$id]);
    }

    /**
     * Get all records
     *
     * @param int|null $limit Limit
     * @param int $offset Offset
     * @param string|null $orderBy Order by column
     * @param string $order Order direction (ASC/DESC)
     * @return array
     */
    public function all(?int $limit = null, int $offset = 0, ?string $orderBy = null, string $order = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($this->useSoftDeletes) {
            $sql .= " WHERE {$this->deletedAt} IS NULL";
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->select($sql);
    }

    /**
     * Get records with custom where clause
     *
     * @param string $where WHERE clause
     * @param array $params Query parameters
     * @param int|null $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function where(string $where, array $params = [], ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";

        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAt} IS NULL";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->select($sql, $params);
    }

    /**
     * Get single record with custom where clause
     *
     * @param string $where WHERE clause
     * @param array $params Query parameters
     * @return array|false
     */
    public function whereOne(string $where, array $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";

        if ($this->useSoftDeletes) {
            $sql .= " AND {$this->deletedAt} IS NULL";
        }

        $sql .= " LIMIT 1";

        return $this->db->selectOne($sql, $params);
    }

    /**
     * Create new record
     *
     * @param array $data Record data
     * @return int Last insert ID
     */
    public function create(array $data): int
    {
        if ($this->useTimestamps) {
            $data[$this->createdAt] = date(DATETIME_FORMAT);
            $data[$this->updatedAt] = date(DATETIME_FORMAT);
        }

        return $this->db->insert($this->table, $data);
    }

    /**
     * Update record by ID
     *
     * @param int $id Record ID
     * @param array $data Update data
     * @return int Number of affected rows
     */
    public function update(int $id, array $data): int
    {
        if ($this->useTimestamps) {
            $data[$this->updatedAt] = date(DATETIME_FORMAT);
        }

        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Delete record by ID
     *
     * @param int $id Record ID
     * @param bool $force Force delete (bypass soft delete)
     * @return int Number of affected rows
     */
    public function delete(int $id, bool $force = false): int
    {
        if ($this->useSoftDeletes && !$force) {
            // Soft delete
            return $this->update($id, [$this->deletedAt => date(DATETIME_FORMAT)]);
        } else {
            // Hard delete
            return $this->db->delete(
                $this->table,
                "{$this->primaryKey} = ?",
                [$id]
            );
        }
    }

    /**
     * Restore soft deleted record
     *
     * @param int $id Record ID
     * @return int Number of affected rows
     */
    public function restore(int $id): int
    {
        if (!$this->useSoftDeletes) {
            return 0;
        }

        return $this->db->update(
            $this->table,
            [$this->deletedAt => null],
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Count records
     *
     * @param string|null $where WHERE clause
     * @param array $params Query parameters
     * @return int
     */
    public function count(?string $where = null, array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";

        $conditions = [];
        if ($where) {
            $conditions[] = $where;
        }

        if ($this->useSoftDeletes) {
            $conditions[] = "{$this->deletedAt} IS NULL";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $result = $this->db->selectOne($sql, $params);
        return (int)$result['count'];
    }

    /**
     * Check if record exists
     *
     * @param string $where WHERE clause
     * @param array $params Query parameters
     * @return bool
     */
    public function exists(string $where, array $params = []): bool
    {
        return $this->count($where, $params) > 0;
    }

    /**
     * Get paginated records
     *
     * @param int $page Page number
     * @param int $perPage Records per page
     * @param string|null $where WHERE clause
     * @param array $params Query parameters
     * @param string|null $orderBy Order by column
     * @param string $order Order direction
     * @return array
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $where = null, array $params = [], ?string $orderBy = null, string $order = 'ASC'): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}";

        $conditions = [];
        if ($where) {
            $conditions[] = $where;
        }

        if ($this->useSoftDeletes) {
            $conditions[] = "{$this->deletedAt} IS NULL";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->db->select($sql, $params);
        $total = $this->count($where, $params);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }

    /**
     * Execute raw SQL query
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        return $this->db->query($sql, $params);
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}
