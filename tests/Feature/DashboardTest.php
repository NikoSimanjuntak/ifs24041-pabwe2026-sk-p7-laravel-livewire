<?php

namespace Tests\Feature;

use App\Features\Dashboard\Livewire\Dashboard;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::create([
            'name' => 'Member', 'username' => 'member', 'email' => 'm@example.test',
            'password' => 'password', 'role' => 'member',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_is_forbidden(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'username' => 'admin', 'email' => 'a@example.test',
            'password' => 'password', 'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
    }

    public function test_member_sees_own_stock_summary(): void
    {
        $member = $this->member();

        foreach ([[10, 5], [3, 5], [0, 5]] as $i => [$qty, $min]) {
            $member->stocks()->create([
                'name' => "Barang $i", 'sku' => "SKU-$i", 'quantity' => $qty, 'unit' => 'pcs',
                'price' => 1000, 'min_stock' => $min,
                'status' => StockStatus::fromQuantity($qty, $min),
            ]);
        }

        $this->actingAs($member);

        $this->get('/dashboard')->assertOk();

        Livewire::test(Dashboard::class)
            ->assertViewHas('total', 3)
            ->assertViewHas('available', 1)
            ->assertViewHas('low', 1)
            ->assertViewHas('out', 1);
    }
}
