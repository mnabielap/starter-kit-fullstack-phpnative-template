<?php

namespace App\Core;

use PDO;
use Exception;

abstract class Model
{
    protected static $pdo;
    protected $table;

    public function __construct()
    {
        if (!self::$pdo) {
            $config = require __DIR__ . '/../../config/database.php';
            $default = $config['default'];
            $dbConfig = $config['connections'][$default];

            try {
                if ($default === 'sqlite') {
                    $dsn = "sqlite:" . $dbConfig['database'];
                    self::$pdo = new PDO($dsn, null, null, $dbConfig['options']);
                } else {
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                    self::$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
                }
            } catch (Exception $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
    }

    public function create(array $data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        $stmt = self::$pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        
        $id = self::$pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById($id)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findOne(array $conditions)
    {
        $whereClause = [];
        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = :$key";
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause) . " LIMIT 1";
        
        $stmt = self::$pdo->prepare($sql);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function find(array $conditions = [], array $options = [])
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "$key = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        if (isset($options['sort'])) {
            $sql .= " ORDER BY " . $options['sort'];
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        $stmt = self::$pdo->prepare($sql);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateById($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        
        return $this->findById($id);
    }

    public function deleteById($id)
    {
        $stmt = self::$pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function deleteMany(array $conditions)
    {
        $whereClause = [];
        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = :$key";
        }
        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = self::$pdo->prepare($sql);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    public function paginate(array $filters, array $options)
    {
        $page = isset($options['page']) ? (int)$options['page'] : 1;
        $limitStr = isset($options['limit']) ? $options['limit'] : 10;
        
        // Handle "All" limit
        $isAll = ($limitStr === 'all' || $limitStr === -1);
        $limit = $isAll ? 1000000 : (int)$limitStr;
        $offset = ($page - 1) * $limit;
        
        // Base SQL
        $sqlWhere = [];
        $params = [];

        // 1. Handle strict filters (exact match)
        foreach ($filters as $key => $value) {
            $sqlWhere[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        // 2. Handle Search Query (LIKE) with Unique Placeholders
        if (!empty($options['search']) && !empty($options['searchFields'])) {
            $searchGroup = [];
            foreach ($options['searchFields'] as $index => $field) {
                // Sanitize field name
                if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                    // Create a unique parameter name for each field (e.g., :search_0, :search_1)
                    $paramName = ":search_" . $index;
                    $searchGroup[] = "$field LIKE $paramName";
                    $params[$paramName] = "%" . $options['search'] . "%";
                }
            }
            if (!empty($searchGroup)) {
                $sqlWhere[] = "(" . implode(' OR ', $searchGroup) . ")";
            }
        }

        // Construct WHERE clause
        $whereSql = "";
        if (!empty($sqlWhere)) {
            $whereSql = " WHERE " . implode(' AND ', $sqlWhere);
        }

        // --- Execute Count ---
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}" . $whereSql;
        $stmtCount = self::$pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $totalResults = $stmtCount->fetch()['total'];

        // --- Execute Data Fetch ---
        $sql = "SELECT * FROM {$this->table}" . $whereSql;

        // Sorting
        if (isset($options['sortBy'])) {
             $parts = explode(':', $options['sortBy']);
             $field = $parts[0];
             $dir = isset($parts[1]) && strtolower($parts[1]) === 'asc' ? 'ASC' : 'DESC';
             if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                 $sql .= " ORDER BY $field $dir";
             } else {
                 $sql .= " ORDER BY created_at DESC";
             }
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        // Limit
        if (!$isAll) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = self::$pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if (!$isAll) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        $totalPages = $isAll ? 1 : ceil($totalResults / $limit);

        return [
            'results' => $results,
            'page' => $page,
            'limit' => $limitStr,
            'totalPages' => $totalPages,
            'totalResults' => $totalResults
        ];
    }
}