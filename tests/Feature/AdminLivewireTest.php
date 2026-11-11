<?php

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_shows_members_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']));
        User::factory()->create(['name' => 'Member Satu']);

        Livewire::test(UserList::class)
            ->assertSee('Member Satu')
            ->assertDontSee('Admin Utama');
    }

    public function test_admin_user_stock_list_shows_stocks_or_empty_state(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $member = User::factory()->create(['name' => 'Budi']);
        $empty = User::factory()->create();
        Stock::factory()->create(['user_id' => $member->id, 'name' => 'Barang Budi']);

        Livewire::test(AdminUserStockList::class, ['user' => $member])->assertSee('Barang Budi');
        Livewire::test(AdminUserStockList::class, ['user' => $empty])->assertSee('Tidak ada stok.');
    }

    public function test_admin_user_stock_list_rejects_non_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(AdminUserStockList::class, ['user' => $admin])->assertNotFound();
    }

    public function test_service_methods(): void
    {
        $member = User::factory()->create();
        Stock::factory()->count(2)->create(['user_id' => $member->id]);

        $service = new AdminUserService();

        $this->assertSame(1, $service->users()->total());
        $this->assertSame(2, $service->stocks($member)->total());
    }
}
