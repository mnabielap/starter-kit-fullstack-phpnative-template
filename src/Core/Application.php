<?php

namespace App\Core;

class Application
{
    public $router;
    public $request;
    public $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    public function run()
    {
        // Load Routes
        require_once __DIR__ . '/../../routes/web.php';
        require_once __DIR__ . '/../../routes/api.php';

        $this->router->resolve();
    }
}