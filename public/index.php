<?php

/**
 * Entry Point
 */

use App\Core\Application;
use App\Utils\Helper;

// 1. Load Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Environment Variables
Helper::loadEnv(__DIR__ . '/../.env');

// 3. Set Global Security Headers (Equivalent to Helmet)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https: data:;');

// 4. Handle CORS (Equivalent to cors)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 5. Initialize and Run Application
try {
    $app = new Application();
    $app->run();
} catch (Exception $e) {
    // Basic fallback error handling
    if (Helper::env('APP_ENV') === 'development') {
        echo "<h1>Fatal Error</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        require_once __DIR__ . '/../views/errors/500.php';
    }
}