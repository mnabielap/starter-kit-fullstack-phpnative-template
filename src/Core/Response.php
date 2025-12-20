<?php

namespace App\Core;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function json($data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit; // Stop execution after sending JSON
    }

    public function send(string $content, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        echo $content;
    }

    public function render(string $view, array $params = [], string $layout = 'main'): void
    {
        View::render($view, $params, $layout);
    }
}