<?php

namespace App\Core;
use App\Middlewares\CSRFMiddleware;

class Router
{
    protected $routes = [];
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }
    
    public function patch($path, $callback)
    {
        $this->routes['patch'][$path] = $callback;
    }
    
    public function delete($path, $callback)
    {
        $this->routes['delete'][$path] = $callback;
    }

    public function resolve()
    {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        if (in_array($method, ['post', 'put', 'patch', 'delete'])) {
            (new CSRFMiddleware())->handle($this->request, $this->response);
        }
        
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/:[a-zA-Z0-9_]+/', '([a-zA-Z0-9_-]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                
                preg_match_all('/:[a-zA-Z0-9_]+/', $route, $paramNames);
                $params = [];
                foreach ($paramNames[0] as $index => $name) {
                    $params[substr($name, 1)] = $matches[$index] ?? null;
                }
                $this->request->setParams($params);

                if (is_array($callback)) {
                    $controller = new $callback[0]();
                    $methodName = $callback[1];
                    return call_user_func([$controller, $methodName], $this->request, $this->response);
                }

                return call_user_func($callback, $this->request, $this->response);
            }
        }

        $this->response->setStatusCode(404);
        if (strpos($path, '/v1') === 0) { 
             $this->response->json(['code' => 404, 'message' => 'Not found']);
        } else {
             $this->response->render('errors/404');
        }
    }
}