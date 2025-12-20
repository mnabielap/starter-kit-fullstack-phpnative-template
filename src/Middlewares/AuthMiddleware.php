<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Utils\Helper;
use App\Models\User;

class AuthMiddleware
{
    // Role Rights Mapping
    private $roleRights = [
        'user' => [],
        'admin' => ['getUsers', 'manageUsers'],
    ];

    public function handle(Request $req, Response $res, array $requiredRights = [])
    {
        $token = $this->getBearerToken($req);

        if (!$token) {
            $res->json(['code' => 401, 'message' => 'Please authenticate'], 401);
        }

        try {
            $payload = $this->verifyJwt($token);
            
            // Check Token Type
            if (!isset($payload['type']) || $payload['type'] !== 'access') {
                 throw new \Exception('Invalid token type');
            }

            // Fetch User
            $userModel = new User();
            $user = $userModel->findById($payload['sub']);

            if (!$user) {
                throw new \Exception('User not found');
            }

            // Attach user to request
            $req->user = $user;

            // Check Rights/Permissions
            if (!empty($requiredRights)) {
                $userRights = $this->roleRights[$user['role']] ?? [];
                $hasRequiredRights = true;
                
                foreach ($requiredRights as $right) {
                    if (!in_array($right, $userRights)) {
                        $hasRequiredRights = false;
                        break;
                    }
                }

                // Admins/Users can access their own data, but 'manageUsers' is strict
                if (!$hasRequiredRights) {
                    // Specific logic: Allow if accessing own ID (simple check)
                    $params = $req->getParams();
                    if (isset($params['userId']) && $params['userId'] == $user['id'] && empty($requiredRights)) {
                        // Allow own access if no specific rights requested (basic auth)
                    } else {
                        $res->json(['code' => 403, 'message' => 'Forbidden'], 403);
                    }
                }
            }

        } catch (\Exception $e) {
            $res->json(['code' => 401, 'message' => 'Please authenticate'], 401);
        }
    }

    private function getBearerToken(Request $req)
    {
        $headers = getallheaders(); // PHP Native function
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    // Simple HS256 Verify Implementation (No external lib)
    private function verifyJwt($token)
    {
        $secret = Helper::env('JWT_SECRET');
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \Exception('Invalid Token Structure');
        }

        [$header64, $payload64, $sig64] = $parts;

        $sig = $this->base64UrlDecode($sig64);
        $expectedSig = hash_hmac('sha256', "$header64.$payload64", $secret, true);

        if (!hash_equals($expectedSig, $sig)) {
             throw new \Exception('Invalid Signature');
        }

        return json_decode($this->base64UrlDecode($payload64), true);
    }

    private function base64UrlDecode($data)
    {
        $urlUnsafeData = strtr($data, '-_', '+/');
        $paddedData = str_pad($urlUnsafeData, strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($paddedData);
    }
}