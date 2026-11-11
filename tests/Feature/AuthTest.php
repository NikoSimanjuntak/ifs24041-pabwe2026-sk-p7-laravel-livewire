<?php

namespace Tests\Feature;

use App\Features\Auth\Livewire\Login;
use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_root_redirects_by_role(): void
    {
        $this->get('/')->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/')->assertRedirect('/stocks');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/')->assertRedirect('/admin/users');
    }

    public function test_member_can_login(): void
    {
        User::factory()->create(['username' => 'budi', 'role' => 'member']);

        Livewire::test(Login::class)
            ->set('username', 'budi')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/stocks');

        $this->assertAuthenticated();
    }

    public function test_admin_can_login(): void
    {
        User::factory()->create(['username' => 'boss', 'role' => 'admin']);

        Livewire::test(Login::class)
            ->set('username', 'boss')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/admin/users');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['username' => 'budi']);

        Livewire::test(Login::class)
            ->set('username', 'budi')
            ->set('password', 'salah')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    public function test_login_requires_fields(): void
    {
        Livewire::test(Login::class)
            ->call('login')
            ->assertHasErrors(['username', 'password']);
    }

    public function test_user_can_register_as_member(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('username', 'siti')
            ->set('email', 'siti@example.test')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('register')
            ->assertRedirect('/stocks');

        $this->assertDatabaseHas('users', ['username' => 'siti', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'siti', 'email' => 'siti@example.test']);

        Livewire::test(Register::class)
            ->set('name', '')
            ->set('username', 'siti')
            ->set('email', 'siti@example.test')
            ->set('password', 'pendek')
            ->set('password_confirmation', 'beda')
            ->call('register')
            ->assertHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get('/stocks')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_authenticated_user_cannot_open_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect();
    }
}
