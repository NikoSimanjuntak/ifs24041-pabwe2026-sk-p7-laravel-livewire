<?php

namespace App\Features\Auth\Livewire;

use Livewire\Component;
use App\Features\Auth\Actions\RegisterUserAction;

class Register extends Component
{
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(RegisterUserAction $action)
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $action->execute($data);

        auth()->login($user);

        session()->regenerate();

        return redirect('/stocks');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app');
    }
}
