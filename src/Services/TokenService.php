<?php

namespace App\Services;

use App\Models\Token;
use App\Models\User;
use App\Utils\Helper;
use App\Utils\ApiError;
use DateTime;

class TokenService
{
    private $tokenModel;
    private $userModel;

    public function __construct()
    {
        $this->tokenModel = new Token();
        $this->userModel = new User();
    }

    /**
     * Generate JWT Token (HMAC SHA256)
     */
    public function generateToken(string $userId, DateTime $expires, string $type, string $secret = null): string
    {
        $secret = $secret ?? Helper::env('JWT_SECRET');
        
        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => $expires->getTimestamp(),
            'type' => $type
        ];

        return $this->signJwt($payload, $secret);
    }

    /**
     * Save token to database
     */
    public function saveToken(string $token, string $userId, DateTime $expires, string $type, bool $blacklisted = false)
    {
        return $this->tokenModel->create([
            'token' => $token,
            'user' => $userId,
            'expires' => $expires->format('Y-m-d H:i:s'),
            'type' => $type,
            'blacklisted' => $blacklisted ? 1 : 0
        ]);
    }

    /**
     * Verify token and return token doc
     */
    public function verifyToken(string $token, string $type)
    {
        try {
            $payload = $this->verifyJwtSignature($token);
        } catch (\Exception $e) {
            throw new \Exception('Token invalid');
        }

        $tokenDoc = $this->tokenModel->findToken($token, $type, false);
        if (!$tokenDoc) {
            throw new \Exception('Token not found');
        }
        
        return $tokenDoc;
    }

    /**
     * Generate Access and Refresh Tokens
     */
    public function generateAuthTokens($user): array
    {
        $accessTokenExpires = (new DateTime())->modify('+' . Helper::env('JWT_ACCESS_EXPIRATION_MINUTES', 30) . ' minutes');
        $accessToken = $this->generateToken($user['id'], $accessTokenExpires, 'access');

        $refreshTokenExpires = (new DateTime())->modify('+' . Helper::env('JWT_REFRESH_EXPIRATION_DAYS', 30) . ' days');
        $refreshToken = $this->generateToken($user['id'], $refreshTokenExpires, 'refresh');
        
        $this->saveToken($refreshToken, $user['id'], $refreshTokenExpires, 'refresh');

        return [
            'access' => [
                'token' => $accessToken,
                'expires' => $accessTokenExpires->format(DateTime::ATOM),
            ],
            'refresh' => [
                'token' => $refreshToken,
                'expires' => $refreshTokenExpires->format(DateTime::ATOM),
            ]
        ];
    }

    public function generateResetPasswordToken(string $email): string
    {
        $user = $this->userModel->findOne(['email' => $email]);
        if (!$user) {
            throw new ApiError(404, 'No users found with this email');
        }
        
        $expires = (new DateTime())->modify('+' . Helper::env('JWT_RESET_PASSWORD_EXPIRATION_MINUTES', 10) . ' minutes');
        $token = $this->generateToken($user['id'], $expires, 'resetPassword');
        
        $this->saveToken($token, $user['id'], $expires, 'resetPassword');
        return $token;
    }
    
    public function generateVerifyEmailToken($user): string
    {
        $expires = (new DateTime())->modify('+' . Helper::env('JWT_VERIFY_EMAIL_EXPIRATION_MINUTES', 10) . ' minutes');
        $token = $this->generateToken($user['id'], $expires, 'verifyEmail');
        
        $this->saveToken($token, $user['id'], $expires, 'verifyEmail');
        return $token;
    }

    // --- JWT Helper Methods (Native PHP) ---

    private function signJwt(array $payload, string $secret): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        $base64Signature = $this->base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    private function verifyJwtSignature(string $token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new \Exception("Invalid Token");

        $secret = Helper::env('JWT_SECRET');
        [$header64, $payload64, $sig64] = $parts;

        $sig = $this->base64UrlDecode($sig64);
        $expectedSig = hash_hmac('sha256', "$header64.$payload64", $secret, true);

        if (!hash_equals($expectedSig, $sig)) throw new \Exception("Invalid Signature");

        return json_decode($this->base64UrlDecode($payload64), true);
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}