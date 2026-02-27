<?php

namespace App\Services;

use App\Dtos\User\UserData;
use App\Interfaces\UserServiceInterface;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct()
    {
    }

    protected function model()
    {
        return new User();
    }

    public function create(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->model()
                ->create([
                    'name'     => $data->name,
                    'email'    => strtolower($data->email),
                    'password' => Hash::make($data->password),
                    'document' => preg_replace('/[^0-9]/', '', $data->document),
                ]);

            $account = Account::create();

            $user->userAccounts()->create(['account_id' => $account->id]);

            return $user->load(['accounts']);
        });
    }

    public function show(): User
    {
        return $this->model()
            ->findOrFail(Auth::id())
            ->load(['accounts']);
    }
}
