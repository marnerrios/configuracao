<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    private $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }
    
    public function login(Request $request)
    {
        return $this->service->login($request);
    }

    public function refresh()
    {
        return $this->service->refresh();
    }
    public function logout()
    {
        return $this->service->logout();
    }

    public function loginForce(Request $request)
    {
        return $this->service->loginForce($request);
    }
}
