<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function execute(
        string $username,
        string $password,
        bool $remember = false
    ): void {
        if (!Auth::attempt([
            'username' => $username,
            'password' => $password,
        ], $remember)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        session()->regenerate();
    }
}
