<?php

namespace App\Features\Auth\Livewire;

use Livewire\Component;
use App\Features\Auth\Actions\LoginUserAction;

class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $remember = false;

    public function login(LoginUserAction $action)
    {
        $data = $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $action->execute(
            $data['username'],
            $data['password'],
            $this->remember
        );

        $user = auth()->user();

        return redirect()->intended(
            $user->role === 'admin' ? '/admin/users' : '/stocks'
        );
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app');
    }
}
