<?php

use App\Utils\Helper;

return [
    'name' => Helper::env('APP_NAME', 'StarterKit'),
    'env' => Helper::env('APP_ENV', 'development'),
    'url' => Helper::env('APP_URL', 'http://localhost:3000'),
    'timezone' => 'UTC',
    'locale' => 'en',
];