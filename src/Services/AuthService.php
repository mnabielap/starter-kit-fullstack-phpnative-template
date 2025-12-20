<?php

namespace App\Services;

use App\Models\User;
use App\Models\Token;
use App\Services\TokenService;
use App\Services\UserService;
use App\Utils\ApiError;

class AuthService
{
    private $userService;
    private $tokenService;
    private $tokenModel;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->tokenService = new TokenService();
        $this->tokenModel = new Token();
    }

    public function loginUserWithEmailAndPassword(string $email, string $password)
    {
        $user = $this->userService->getUserByEmail($email);
        $userModel = new User(); // Need instance to call method

        if (!$user || !$userModel->isPasswordMatch($password, $user['password'])) {
            throw new ApiError(401, 'Incorrect email or password');
        }
        
        return $user;
    }

    public function logout(string $refreshToken)
    {
        $refreshTokenDoc = $this->tokenModel->findToken($refreshToken, 'refresh', false);
        if (!$refreshTokenDoc) {
            throw new ApiError(404, 'Not found');
        }
        $this->tokenModel->deleteById($refreshTokenDoc['id']);
    }

    public function refreshAuth(string $refreshToken)
    {
        try {
            $refreshTokenDoc = $this->tokenService->verifyToken($refreshToken, 'refresh');
            $user = $this->userService->getUserById($refreshTokenDoc['user']);
            
            if (!$user) throw new \Exception();
            
            $this->tokenModel->deleteById($refreshTokenDoc['id']);
            return $this->tokenService->generateAuthTokens($user);
        } catch (\Exception $e) {
            throw new ApiError(401, 'Please authenticate');
        }
    }

    public function resetPassword(string $resetPasswordToken, string $newPassword)
    {
        try {
            $resetTokenDoc = $this->tokenService->verifyToken($resetPasswordToken, 'resetPassword');
            $user = $this->userService->getUserById($resetTokenDoc['user']);
            
            if (!$user) throw new \Exception();
            
            $this->userService->updateUserById($user['id'], ['password' => $newPassword]);
            $this->tokenModel->deleteMany(['user' => $user['id'], 'type' => 'resetPassword']);
        } catch (\Exception $e) {
            throw new ApiError(401, 'Password reset failed');
        }
    }

    public function verifyEmail(string $verifyEmailToken)
    {
        try {
            $verifyTokenDoc = $this->tokenService->verifyToken($verifyEmailToken, 'verifyEmail');
            $user = $this->userService->getUserById($verifyTokenDoc['user']);
            
            if (!$user) throw new \Exception();
            
            $this->tokenModel->deleteMany(['user' => $user['id'], 'type' => 'verifyEmail']);
            $this->userService->updateUserById($user['id'], ['is_email_verified' => 1]); // Mapping isEmailVerified to snake_case column commonly used
        } catch (\Exception $e) {
            throw new ApiError(401, 'Email verification failed');
        }
    }
}