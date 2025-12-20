<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';

    /**
     * Override create to hash password
     */
    public function create(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        return parent::create($data);
    }

    /**
     * Override updateById to hash password if changed
     */
    public function updateById($id, array $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        
        return parent::updateById($id, $data);
    }

    /**
     * Check if email is taken
     */
    public function isEmailTaken(string $email, $excludeUserId = null): bool
    {
        $sql = "SELECT count(*) as count FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeUserId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeUserId;
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Check if password matches
     */
    public function isPasswordMatch(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Transform data for JSON response (remove private fields)
     */
    public function transform($user)
    {
        if (!$user) return null;
        
        // Remove private fields
        unset($user['password']);
        
        // Ensure ID is string (standard for API consistency)
        $user['id'] = (string) $user['id'];
        
        return $user;
    }
}