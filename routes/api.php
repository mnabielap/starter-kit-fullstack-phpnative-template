<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;

$router = $this->router; // Access router from Application scope
$authMiddleware = new AuthMiddleware();

// --- Auth Routes ---
$router->post('/v1/auth/register', [AuthController::class, 'register']);
$router->post('/v1/auth/login', [AuthController::class, 'login']);
$router->post('/v1/auth/logout', [AuthController::class, 'logout']);
$router->post('/v1/auth/refresh-tokens', [AuthController::class, 'refreshTokens']);
$router->post('/v1/auth/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/v1/auth/reset-password', [AuthController::class, 'resetPassword']);

// --- User Routes ---
$router->post('/v1/users', function($req, $res) use ($authMiddleware) {
    $authMiddleware->handle($req, $res, ['manageUsers']); // Auth & Role check
    (new UserController())->createUser($req, $res);
});

$router->get('/v1/users', function($req, $res) use ($authMiddleware) {
    $authMiddleware->handle($req, $res, ['getUsers']);
    (new UserController())->getUsers($req, $res);
});

$router->get('/v1/users/:userId', function($req, $res) use ($authMiddleware) {
    $authMiddleware->handle($req, $res, ['getUsers']);
    (new UserController())->getUser($req, $res);
});

$router->patch('/v1/users/:userId', function($req, $res) use ($authMiddleware) {
    $authMiddleware->handle($req, $res, ['manageUsers']);
    (new UserController())->updateUser($req, $res);
});

$router->delete('/v1/users/:userId', function($req, $res) use ($authMiddleware) {
    $authMiddleware->handle($req, $res, ['manageUsers']);
    (new UserController())->deleteUser($req, $res);
});