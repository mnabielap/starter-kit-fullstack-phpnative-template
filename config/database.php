<?php

use App\Utils\Helper;

return [
    'default' => Helper::env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Helper::env('DB_HOST', '127.0.0.1'),
            'port' => Helper::env('DB_PORT', '3306'),
            'database' => Helper::env('DB_DATABASE', 'starter_kit'),
            'username' => Helper::env('DB_USERNAME', 'root'),
            'password' => Helper::env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/' . Helper::env('DB_SQLITE_PATH', 'db.sqlite'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ],
    ]
];