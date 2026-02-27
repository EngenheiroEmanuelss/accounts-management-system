<?php

namespace App\Interfaces;

use App\Dtos\Auth\AuthData;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    public function login(AuthData $data);

    public function logout(Request $request);
}
