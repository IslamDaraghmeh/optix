<?php
/**
 * Database Helper Class
 *
 * PDO wrapper class providing CRUD operations and transaction management
 * Implements singleton pattern for database connection
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * @var PDO|null Database connection instance
     */
    private static ?PDO $connection = null;

    /**
     * @var Database|null Singleton instance
     */
    private static ?Database $instance = null;

    /**
     * @var array Database configuration
     */
    private array $config;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->config = require CONFIG_PATH . '/database.php';
        $this->connect();
    }

    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     *
     * @return void
     * @throws PDOException
     */
    private function connect(): void
    {
        if (self::$connection !== null) {
            return;
        }

        try {
            $default = $this->config['default'];
            $config = $this->config['connections'][$default];

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            $this->logInfo('Database connection established');
        } catch (PDOException $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return self::$connection;
    }

    /**
     * Execute a SQL query with parameters
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return PDOStatement
     * @throws PDOException
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Select multiple rows
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Select a single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false
     */
    public function selectOne(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Insert data into table
     *
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->query($sql, array_values($data));
        return (int)self::$connection->lastInsertId();
    }

    /**
     * Update data in table
     *
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause
     * @param array $whereParams WHERE clause parameters
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete data from table
     *
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $whereParams WHERE clause parameters
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return self::$connection->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return self::$connection->commit();
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return self::$connection->rollBack();
    }

    /**
     * Check if in transaction
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return self::$connection->inTransaction();
    }

    /**
     * Execute transaction with callback
     *
     * @param callable $callback Transaction callback
     * @return mixed Callback return value
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            $this->logError('Transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get last insert ID
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return self::$connection->lastInsertId();
    }

    /**
     * Count rows in table with optional where clause
     *
     * @param string $table Table name
     * @param string|null $where WHERE clause
     * @param array $params Query parameters
     * @return int
     */
    public function count(string $table, ?string $where = null, array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        $result = $this->selectOne($sql, $params);
        return (int)$result['count'];
    }

    /**
     * Check if record exists
     *
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Query parameters
     * @return bool
     */
    public function exists(string $table, string $where, array $params = []): bool
    {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Log information message
     *
     * @param string $message
     * @return void
     */
    private function logInfo(string $message): void
    {
        if (APP_DEBUG) {
            error_log('[Database INFO] ' . $message);
        }
    }

    /**
     * Log error message
     *
     * @param string $message
     * @return void
     */
    private function logError(string $message): void
    {
        error_log('[Database ERROR] ' . $message);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
