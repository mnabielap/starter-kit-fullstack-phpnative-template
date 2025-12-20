<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(Request $req, Response $res)
    {
        // Simple dashboard view
        // The view will handle checking for the JWT token via JS
        $res->render('dashboard/index', ['title' => 'Dashboard']);
    }
}