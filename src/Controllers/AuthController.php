<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\TokenService;
use App\Services\UserService;
use App\Services\EmailService;
use App\Utils\Validator;
use App\Utils\ApiError;
use App\Models\User;

class AuthController extends Controller
{
    private $authService;
    private $userService;
    private $tokenService;
    private $emailService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userService = new UserService();
        $this->tokenService = new TokenService();
        $this->emailService = new EmailService();
    }

    // --- API Endpoints (JSON) ---

    public function register(Request $req, Response $res)
    {
        $body = $req->getBody();
        $validation = Validator::validate($body, [
            'email' => 'required|email',
            'password' => 'required|password',
            'name' => 'required'
        ]);

        if ($validation !== true) {
            $res->json(['code' => 400, 'message' => $validation], 400);
        }

        try {
            $user = $this->userService->createUser($body);
            $tokens = $this->tokenService->generateAuthTokens($user);
            
            // Transform user to remove password
            $userModel = new User();
            $res->json(['user' => $userModel->transform($user), 'tokens' => $tokens], 201);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function login(Request $req, Response $res)
    {
        $body = $req->getBody();
        $validation = Validator::validate($body, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validation !== true) {
            $res->json(['code' => 400, 'message' => $validation], 400);
        }

        try {
            $user = $this->authService->loginUserWithEmailAndPassword($body['email'], $body['password']);
            $tokens = $this->tokenService->generateAuthTokens($user);
            
            $userModel = new User();
            $res->json(['user' => $userModel->transform($user), 'tokens' => $tokens]);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function logout(Request $req, Response $res)
    {
        $body = $req->getBody();
        if (!isset($body['refreshToken'])) {
             $res->json(['code' => 400, 'message' => 'refreshToken is required'], 400);
        }

        try {
            $this->authService->logout($body['refreshToken']);
            $res->setStatusCode(204); // No Content
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function refreshTokens(Request $req, Response $res)
    {
        $body = $req->getBody();
        if (!isset($body['refreshToken'])) {
             $res->json(['code' => 400, 'message' => 'refreshToken is required'], 400);
        }

        try {
            $tokens = $this->authService->refreshAuth($body['refreshToken']);
            $res->json($tokens);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function forgotPassword(Request $req, Response $res)
    {
        $body = $req->getBody();
        $validation = Validator::validate($body, ['email' => 'required|email']);
        
        if ($validation !== true) {
            $res->json(['code' => 400, 'message' => $validation], 400);
        }

        try {
            $resetPasswordToken = $this->tokenService->generateResetPasswordToken($body['email']);
            $this->emailService->sendResetPasswordEmail($body['email'], $resetPasswordToken);
            $res->setStatusCode(204);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function resetPassword(Request $req, Response $res)
    {
        $query = $req->getQuery();
        $body = $req->getBody();

        if (!isset($query['token']) || !isset($body['password'])) {
            $res->json(['code' => 400, 'message' => 'Token and Password required'], 400);
        }

        try {
            $this->authService->resetPassword($query['token'], $body['password']);
            $res->setStatusCode(204);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }
    
    // --- View Endpoints (Fullstack) ---

    public function viewLogin(Request $req, Response $res)
    {
        $res->render('auth/login', [], 'auth');
    }

    public function viewRegister(Request $req, Response $res)
    {
        $res->render('auth/register', [], 'auth');
    }
}