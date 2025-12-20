<?php

namespace App\Services;

use App\Models\User;
use App\Utils\ApiError;

class UserService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function createUser(array $userBody)
    {
        if ($this->userModel->isEmailTaken($userBody['email'])) {
            throw new ApiError(400, 'Email already taken');
        }
        return $this->userModel->create($userBody);
    }

    public function queryUsers(array $filter, array $options)
    {
        return $this->userModel->paginate($filter, $options);
    }

    public function getUserById($id)
    {
        return $this->userModel->findById($id);
    }

    public function getUserByEmail(string $email)
    {
        return $this->userModel->findOne(['email' => $email]);
    }

    public function updateUserById($userId, array $updateBody)
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new ApiError(404, 'User not found');
        }
        
        if (isset($updateBody['email']) && $this->userModel->isEmailTaken($updateBody['email'], $userId)) {
            throw new ApiError(400, 'Email already taken');
        }

        return $this->userModel->updateById($userId, $updateBody);
    }

    public function deleteUserById($userId)
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new ApiError(404, 'User not found');
        }
        return $this->userModel->deleteById($userId);
    }
}