<?php

namespace App\Services;

use App\Dtos\Auth\AuthData;
use App\Interfaces\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class AuthService implements AuthServiceInterface
{
    public function __construct()
    {
    }

    public function login(AuthData $data)
    {
        if (!Auth::attempt($data->toArray())) {
            throw new UnauthorizedException('Invalid credentials', 401);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => [
                "id"       => $user->id,
                "name"     => $user->name,
                "document" => $user->document,
                "email"    => $user->email
            ]
        ];
    }

    public function logout($request)
    {
        $request->user()->currentAccessToken()->delete();

        return ['message' => 'Logout successful'];
    }
}
