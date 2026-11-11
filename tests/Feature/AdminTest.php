<?php

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_user_list_shows_members_only(): void
    {
        User::factory()->create(['role' => 'member', 'name' => 'Member Satu']);

        $this->get('/admin/users')->assertOk()->assertSee('Member Satu');

        Livewire::test(UserList::class)->assertSee('Member Satu');
    }

    public function test_user_stock_list_shows_stocks(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        Stock::factory()->create(['user_id' => $member->id, 'name' => 'Barang Member']);

        $this->get('/admin/users/'.$member->id.'/stocks')->assertOk()->assertSee('Barang Member');

        Livewire::test(AdminUserStockList::class, ['user' => $member])->assertSee('Barang Member');
    }

    public function test_user_stock_list_of_non_member_is_404(): void
    {
        $this->get('/admin/users/'.$this->admin->id.'/stocks')->assertNotFound();
    }

    public function test_member_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/admin/users')->assertForbidden();
    }
}
