<?php

namespace App\Dtos\Auth;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Data;

class AuthData extends Data
{
    #[Email]
    public string $email;
    #[Password(8)]
    public string $password;
}
