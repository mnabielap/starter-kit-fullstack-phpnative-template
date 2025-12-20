<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CSRFMiddleware
{
    public function handle(Request $req, Response $res)
    {
        // Only check on unsafe methods
        if (in_array($req->getMethod(), ['post', 'put', 'patch', 'delete'])) {
            $sessionToken = Session::get('csrf_token');
            $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            $bodyToken = $req->input('csrf_token');

            if (!$sessionToken || ($headerToken !== $sessionToken && $bodyToken !== $sessionToken)) {
                $res->json(['code' => 403, 'message' => 'Invalid CSRF Token'], 403);
            }
        }
    }

    public static function generateToken()
    {
        if (!Session::get('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }
}