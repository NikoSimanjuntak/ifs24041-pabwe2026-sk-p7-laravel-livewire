<?php

namespace Tests\Feature;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create();
        $this->actingAs($this->member);
    }

    public function test_list_renders_and_filters(): void
    {
        Stock::factory()->create(['user_id' => $this->member->id, 'name' => 'Laptop', 'quantity' => 50, 'min_stock' => 5, 'status' => StockStatus::AVAILABLE]);

        Livewire::test(StockList::class)
            ->assertSee('Laptop')
            ->set('search', 'Zzz')
            ->assertDontSee('Laptop')
            ->set('search', '')
            ->set('status', 'out_of_stock')
            ->assertDontSee('Laptop')
            ->set('status', 'available')
            ->assertSee('Laptop');
    }

    public function test_create_opens_modal_and_saves_new_stock(): void
    {
        Livewire::test(StockList::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->set('form.name', 'Kabel')
            ->set('form.quantity', 3)
            ->set('form.min_stock', 5)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('stocks', ['name' => 'Kabel', 'status' => 'low_stock', 'user_id' => $this->member->id]);
    }

    public function test_create_validation_error(): void
    {
        Livewire::test(StockList::class)
            ->call('create')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    public function test_edit_and_update_existing_stock(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'name' => 'Lama', 'quantity' => 10]);

        Livewire::test(StockList::class)
            ->call('edit', $stock->id)
            ->assertSet('editingId', $stock->id)
            ->assertSet('showModal', true)
            ->set('form.name', 'Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Baru', $stock->fresh()->name);
    }

    public function test_adjust_and_delete(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'quantity' => 10, 'min_stock' => 5]);

        Livewire::test(StockList::class)
            ->call('adjust', $stock->id, 5)
            ->call('delete', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_adjust_updates_quantity(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'quantity' => 10, 'min_stock' => 5]);

        Livewire::test(StockList::class)->call('adjust', $stock->id, 5);

        $this->assertSame(15, $stock->fresh()->quantity);
    }

    public function test_cannot_manage_stock_of_other_user(): void
    {
        $other = Stock::factory()->create();

        Livewire::test(StockList::class)
            ->call('edit', $other->id)
            ->assertNotFound();
    }

    public function test_admin_cannot_create_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(StockList::class)
            ->call('create')
            ->set('form.name', 'X')
            ->call('save')
            ->assertForbidden();
    }

    public function test_detail_renders_saves_and_uploads_cover(): void
    {
        Storage::fake('public');
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'name' => 'Lama', 'quantity' => 4, 'min_stock' => 5]);

        $component = Livewire::test(StockDetail::class, ['stock' => $stock])
            ->assertSee('Lama')
            ->set('form.name', 'Baru')
            ->set('cover', UploadedFile::fake()->image('c1.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $first = $stock->fresh()->cover_image_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);

        $component
            ->set('cover', UploadedFile::fake()->image('c2.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($first);
        $this->assertSame('Baru', $stock->fresh()->name);
    }

    public function test_detail_save_without_cover_and_adjust(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'quantity' => 4, 'min_stock' => 5]);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('save')
            ->assertHasNoErrors()
            ->call('adjust', 10);

        $this->assertSame(14, $stock->fresh()->quantity);
        $this->assertSame(StockStatus::AVAILABLE, $stock->fresh()->status);
    }

    public function test_detail_renders_existing_cover_image(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'cover_image_path' => 'stock-covers/x.jpg']);

        Livewire::test(StockDetail::class, ['stock' => $stock])->assertSeeHtml('stock-covers/x.jpg');
    }

    public function test_detail_is_not_viewable_by_other_member(): void
    {
        $stock = Stock::factory()->create();

        Livewire::test(StockDetail::class, ['stock' => $stock])->assertForbidden();
    }
}
