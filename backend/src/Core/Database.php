<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * 資料庫連接管理類
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        $this->config = include __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        $defaultConnection = $this->config['default'];
        $config = $this->config['connections'][$defaultConnection];
        
        if ($defaultConnection === 'sqlite') {
            $dsn = "sqlite:{$config['database']}";
            try {
                $this->connection = new PDO($dsn, null, null, $config['options']);
            } catch (PDOException $e) {
                throw new PDOException("資料庫連接失敗: " . $e->getMessage());
            }
        } else {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            try {
                $this->connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                throw new PDOException("資料庫連接失敗: " . $e->getMessage());
            }
        }
    }

    public function getConnection(): PDO
    {
        // 檢查連接是否仍然有效
        if (!$this->connection) {
            $this->connect();
        }
        
        return $this->connection;
    }

    /**
     * 執行查詢
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params));
            throw new PDOException("查詢執行失敗: " . $e->getMessage());
        }
    }

    /**
     * 獲取單一記錄
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * 獲取多筆記錄
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * 插入記錄
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, array_values($data));
        return (int)$this->connection->lastInsertId();
    }

    /**
     * 更新記錄
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $columns = array_keys($data);
        $setParts = array_map(fn($col) => "`$col` = ?", $columns);
        
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * 刪除記錄
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * 開始事務
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * 提交事務
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * 回滾事務
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * 檢查是否在事務中
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * 獲取最後插入的 ID
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * 構建分頁查詢
     */
    public function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 20): array
    {
        // 計算總記錄數
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_table";
        $totalResult = $this->fetch($countSql, $params);
        $total = (int)$totalResult['total'];
        
        // 計算偏移量
        $offset = ($page - 1) * $perPage;
        
        // 添加 LIMIT 和 OFFSET
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $data = $this->fetchAll($paginatedSql, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ]
        ];
    }

    /**
     * 安全的 LIKE 查詢
     */
    public function escapeLike(string $string): string
    {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }

    /**
     * 構建 WHERE 條件
     */
    public function buildWhere(array $conditions, string $operator = 'AND'): array
    {
        $whereParts = [];
        $params = [];
        $paramCounter = 0;
        
        foreach ($conditions as $column => $value) {
            $paramKey = "param_" . $paramCounter++;
            
            if (is_array($value)) {
                $placeholders = [];
                foreach ($value as $i => $v) {
                    $subParamKey = $paramKey . "_" . $i;
                    $placeholders[] = ":$subParamKey";
                    $params[$subParamKey] = $v;
                }
                $whereParts[] = "`$column` IN (" . implode(', ', $placeholders) . ")";
            } elseif (is_null($value)) {
                $whereParts[] = "`$column` IS NULL";
            } else {
                $whereParts[] = "`$column` = :$paramKey";
                $params[$paramKey] = $value;
            }
        }
        
        $whereClause = empty($whereParts) ? '' : implode(" $operator ", $whereParts);
        
        return [
            'where' => $whereClause,
            'params' => $params
        ];
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}