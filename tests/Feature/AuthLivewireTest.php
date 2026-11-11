<?php

namespace Tests\Feature;

use App\Features\Auth\Livewire\Login;
use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startSession();
    }

    public function test_member_login_redirects_to_stocks(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'secret123']);

        Livewire::test(Login::class)
            ->set('username', 'budi')
            ->set('password', 'secret123')
            ->set('remember', true)
            ->call('login')
            ->assertRedirect('/stocks');

        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_users(): void
    {
        User::factory()->create(['username' => 'boss', 'password' => 'secret123', 'role' => 'admin']);

        Livewire::test(Login::class)
            ->set('username', 'boss')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect('/admin/users');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'secret123']);

        Livewire::test(Login::class)
            ->set('username', 'budi')
            ->set('password', 'salah')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('username', 'siti_01')
            ->set('email', 'siti@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect('/stocks');

        $this->assertDatabaseHas('users', ['username' => 'siti_01', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_validates_input(): void
    {
        Livewire::test(Register::class)
            ->set('name', '')
            ->set('password', 'short')
            ->call('register')
            ->assertHasErrors(['name', 'username', 'email', 'password']);
    }
}
