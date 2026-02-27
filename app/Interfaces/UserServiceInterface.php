<?php

namespace App\Interfaces;

use App\Dtos\User\UserData;
use App\Models\User;

interface UserServiceInterface
{
    public function create(UserData $data): User;

    public function show(): User;
}
