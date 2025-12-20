<?php

namespace App\Utils;

class Helper
{
    public static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    public static function env(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    public static function e(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get the base URL of the application dynamically
     * Handles sub-folders correctly (e.g. localhost/my-app/public)
     */
    public static function baseUrl(string $path = ''): string
    {
        // 1. Detect Protocol
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        
        // 2. Get Host (localhost or domain.com)
        $host = $_SERVER['HTTP_HOST'];

        // 3. Detect Script Directory (where index.php is located)
        // $_SERVER['SCRIPT_NAME'] returns /starter-kit.../public/index.php
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        
        // Fix for Windows backslashes
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // Remove trailing slash if it exists (for root path)
        if ($scriptDir === '/') {
            $scriptDir = '';
        }

        // 4. Construct Base URL
        $base = $protocol . "://" . $host . $scriptDir;

        // 5. Append requested path
        return $base . '/' . ltrim($path, '/');
    }
}