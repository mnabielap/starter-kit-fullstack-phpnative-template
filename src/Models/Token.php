<?php

namespace App\Models;

use App\Core\Model;

class Token extends Model
{
    protected $table = 'tokens';

    /**
     * Find token based on token string, type, and blacklist status
     */
    public function findToken(string $token, string $type, bool $blacklisted = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = :token AND type = :type AND blacklisted = :blacklisted LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':token' => $token,
            ':type' => $type,
            ':blacklisted' => $blacklisted ? 1 : 0
        ]);
        return $stmt->fetch();
    }
}