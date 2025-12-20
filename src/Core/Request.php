<?php

namespace App\Core;

class Request
{
    private $params = [];
    public $user = null;

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // 1. Remove Query String (e.g. ?id=1)
        if ($position = strpos($uri, '?')) {
            $uri = substr($uri, 0, $position);
        }

        // 2. Handle Sub-folder Installation (Fix for XAMPP/Laragon)
        // Get the directory where index.php resides
        $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /my-app/public/index.php
        $scriptDir = dirname($scriptName);     // e.g., /my-app/public

        // Normalize slashes (Windows uses \, URLs use /)
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // If the URI starts with the script directory, remove it
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // Ensure URI always starts with / and handle empty string
        if ($uri === '' || $uri === false) {
            return '/';
        }
        
        // If URI is just named index.php, treat as root
        if ($uri === '/index.php') {
            return '/';
        }

        return $uri;
    }
    
    public function getBody(): array
    {
        $body = [];
        if ($this->getMethod() === 'post' || $this->getMethod() === 'put' || $this->getMethod() === 'patch') {
            // JSON Input
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input)) {
                $body = $input;
            } else {
                // Form Data
                foreach ($_POST as $key => $value) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $body;
    }

    public function getQuery(): array
    {
        $query = [];
        foreach ($_GET as $key => $value) {
            $query[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $query;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function input(string $key)
    {
        $body = $this->getBody();
        $query = $this->getQuery();
        return $body[$key] ?? $query[$key] ?? $this->params[$key] ?? null;
    }
}