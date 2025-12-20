<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\HomeController;

$router = $this->router;

// Home / Dashboard
$router->get('/', [HomeController::class, 'index']);

// Auth Pages
$router->get('/login', [AuthController::class, 'viewLogin']);
$router->get('/register', [AuthController::class, 'viewRegister']);

// User Management Pages (CRUD Views)
$router->get('/users', [UserController::class, 'index']);        // List
$router->get('/users/create', [UserController::class, 'createView']); // Create Form
$router->get('/users/edit', [UserController::class, 'editView']);     // Edit Form