<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\UserService;
use App\Utils\Validator;
use App\Utils\ApiError;
use App\Models\User;

class UserController extends Controller
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function createUser(Request $req, Response $res)
    {
        $body = $req->getBody();
        $validation = Validator::validate($body, [
            'email' => 'required|email',
            'password' => 'required|password',
            'name' => 'required',
            'role' => 'required|valid:user,admin'
        ]);

        if ($validation !== true) {
            $res->json(['code' => 400, 'message' => $validation], 400);
        }

        try {
            $user = $this->userService->createUser($body);
            $userModel = new User();
            $res->json($userModel->transform($user), 201);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function getUsers(Request $req, Response $res)
    {
        $query = $req->getQuery();
        
        // 1. Pagination & Sorting Options
        $options = [
            'page' => $query['page'] ?? 1,
            'limit' => $query['limit'] ?? 10,
            'sortBy' => $query['sortBy'] ?? 'created_at:desc'
        ];

        // 2. Search Logic (Fuzzy Match)
        $search = $query['search'] ?? '';
        $scope = $query['scope'] ?? 'all';

        if (!empty($search)) {
            $options['search'] = $search;
            
            if ($scope === 'all') {
                $options['searchFields'] = ['name', 'email', 'role', 'id'];
            } else {
                $validColumns = ['name', 'email', 'role', 'id'];
                if (in_array($scope, $validColumns)) {
                    $options['searchFields'] = [$scope];
                } else {
                    $options['searchFields'] = ['name'];
                }
            }
        }

        // 3. Strict Filters (Exact Match for Role Dropdown)
        $filters = []; 
        if (isset($query['role']) && !empty($query['role'])) {
            // Only allow valid roles to be filtered
            if (in_array($query['role'], ['user', 'admin'])) {
                $filters['role'] = $query['role'];
            }
        }

        try {
            // Pass strict filters ($filters) AND search options ($options)
            $result = $this->userService->queryUsers($filters, $options);
            
            // Transform results
            $userModel = new User();
            $result['results'] = array_map([$userModel, 'transform'], $result['results']);
            
            $res->json($result);
        } catch (\Exception $e) {
            $res->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUser(Request $req, Response $res)
    {
        $params = $req->getParams();
        try {
            $user = $this->userService->getUserById($params['userId']);
            if (!$user) {
                $res->json(['code' => 404, 'message' => 'User not found'], 404);
            }
            $userModel = new User();
            $res->json($userModel->transform($user));
        } catch (\Exception $e) {
            $res->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $req, Response $res)
    {
        $params = $req->getParams();
        $body = $req->getBody();
        
        try {
            $user = $this->userService->updateUserById($params['userId'], $body);
            $userModel = new User();
            $res->json($userModel->transform($user));
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }

    public function deleteUser(Request $req, Response $res)
    {
        $params = $req->getParams();
        try {
            $this->userService->deleteUserById($params['userId']);
            $res->setStatusCode(204);
        } catch (ApiError $e) {
            $res->json(['code' => $e->statusCode, 'message' => $e->getMessage()], $e->statusCode);
        }
    }
    
    // --- View Endpoint ---
    public function index(Request $req, Response $res)
    {
        $res->render('users/index', [
            'title' => 'User List', 
            'pagetitle' => 'Users'
        ]);
    }

    public function createView(Request $req, Response $res)
    {
        $res->render('users/create', [
            'title' => 'Create User', 
            'pagetitle' => 'Users'
        ]);
    }

    public function editView(Request $req, Response $res)
    {
        $res->render('users/edit', [
            'title' => 'Edit User', 
            'pagetitle' => 'Users'
        ]);
    }
}