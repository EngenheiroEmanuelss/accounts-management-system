<?php

namespace App\Dtos\User;

use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public string $name;

    #[Email, Unique('users', 'email')]
    public string $email;
    #[DigitsBetween(11, 14)]
    public string $document;
    #[Password(min: 8, letters: true, mixedCase: false, numbers: true, symbols: true)]
    public string $password;
    public static function messages(...$args): array
    {
        return [
            'password.min'       => 'Senha deve conter pelo menos 8 caracteres',
            'password.numbers'   => 'Senha deve conter pelo menos um número',
            'password.symbols'   => 'Senha deve conter pelo menos um caracter especial',
            'password.letters'   => 'Senha deve conter pelo menos uma letra',
            'password.mixedCase' => 'Senha deve conter pelo menos uma letra maiuscula',
        ];
    }
}
