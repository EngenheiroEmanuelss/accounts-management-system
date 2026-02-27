<?php

namespace App\Http\Controllers;

use App\Dtos\Auth\AuthData;
use App\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(AuthData $data)
    {
        return response()->json($this->authService->login($data));
    }

    public function logout(Request $request)
    {
        return response()->json($this->authService->logout($request));
    }
}
