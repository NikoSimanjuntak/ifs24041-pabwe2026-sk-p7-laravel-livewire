<?php

namespace Tests\Feature;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StockListTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['role' => 'member']);
        $this->actingAs($this->member);
    }

    private function stock(array $attrs = []): Stock
    {
        return Stock::factory()->create(array_merge(['user_id' => $this->member->id], $attrs));
    }

    public function test_page_renders_and_lists_only_own_stock(): void
    {
        $this->stock(['name' => 'Milik Saya']);
        Stock::factory()->create(['name' => 'Milik Orang Lain']);

        $this->get('/stocks')->assertOk()->assertSee('Milik Saya')->assertDontSee('Milik Orang Lain');
    }

    public function test_search_and_status_filter(): void
    {
        $this->stock(['name' => 'Laptop', 'sku' => 'LAP-1', 'quantity' => 20, 'min_stock' => 5, 'status' => StockStatus::AVAILABLE]);
        $this->stock(['name' => 'Mouse', 'sku' => 'MOU-1', 'quantity' => 0, 'min_stock' => 5, 'status' => StockStatus::OUT_OF_STOCK]);

        Livewire::test(StockList::class)
            ->set('search', 'Laptop')
            ->assertSee('Laptop')->assertDontSee('Mouse')
            ->set('search', '')
            ->set('status', 'out_of_stock')
            ->assertSee('Mouse')->assertDontSee('Laptop');
    }

    public function test_create_opens_modal_with_defaults(): void
    {
        Livewire::test(StockList::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('editingId', null)
            ->assertSet('form.unit', 'pcs');
    }

    public function test_save_creates_stock_with_computed_status(): void
    {
        Livewire::test(StockList::class)
            ->call('create')
            ->set('form.name', 'Kabel')
            ->set('form.quantity', 3)
            ->set('form.min_stock', 5)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('stocks', ['name' => 'Kabel', 'user_id' => $this->member->id, 'status' => 'low_stock']);
    }

    public function test_save_validates(): void
    {
        Livewire::test(StockList::class)
            ->call('create')
            ->set('form.name', '')
            ->set('form.quantity', -1)
            ->call('save')
            ->assertHasErrors(['form.name', 'form.quantity']);
    }

    public function test_edit_and_update(): void
    {
        $stock = $this->stock(['name' => 'Lama', 'quantity' => 10, 'min_stock' => 5]);

        Livewire::test(StockList::class)
            ->call('edit', $stock->id)
            ->assertSet('editingId', $stock->id)
            ->assertSet('form.name', 'Lama')
            ->set('form.name', 'Baru')
            ->set('form.quantity', 0)
            ->call('save');

        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'name' => 'Baru', 'status' => 'out_of_stock']);
    }

    public function test_cannot_edit_other_users_stock(): void
    {
        $other = Stock::factory()->create();

        Livewire::test(StockList::class)->call('edit', $other->id)->assertNotFound();
    }

    public function test_adjust_quantity(): void
    {
        $stock = $this->stock(['quantity' => 5, 'min_stock' => 5, 'status' => StockStatus::LOW_STOCK]);

        Livewire::test(StockList::class)->call('adjust', $stock->id, 3);

        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'quantity' => 8, 'status' => 'available']);
    }

    public function test_adjust_below_zero_fails(): void
    {
        $stock = $this->stock(['quantity' => 1]);

        Livewire::test(StockList::class)->call('adjust', $stock->id, -5)->assertHasErrors('quantity');

        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'quantity' => 1]);
    }

    public function test_delete(): void
    {
        $stock = $this->stock();

        Livewire::test(StockList::class)->call('delete', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_admin_cannot_open_member_stock_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/stocks')->assertForbidden();
    }

    public function test_admin_cannot_modify_stock(): void
    {
        $stock = $this->stock();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->assertFalse(auth()->user()->can('update', $stock));
        $this->assertFalse(auth()->user()->can('create', Stock::class));
    }
}
