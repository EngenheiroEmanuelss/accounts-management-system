<?php

namespace App\Http\Controllers;

use App\Dtos\User\UserData;
use App\Http\Resources\UserResource;
use App\Interfaces\UserServiceInterface;

class UserController extends Controller
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function create(UserData $data)
    {
        return response()->json(new UserResource($this->userService->create($data)), 201);
    }

    public function show()
    {
        return response()->json(new UserResource($this->userService->show()));
    }
}
