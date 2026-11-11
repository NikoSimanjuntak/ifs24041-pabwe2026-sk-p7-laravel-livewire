<?php

namespace Tests\Feature;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $member;
    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['role' => 'member']);
        $this->stock = Stock::factory()->create(['user_id' => $this->member->id, 'quantity' => 10, 'min_stock' => 5]);
        $this->actingAs($this->member);
    }

    public function test_owner_can_view_detail(): void
    {
        $this->get('/stocks/'.$this->stock->id)->assertOk()->assertSee($this->stock->name);
    }

    public function test_other_member_gets_403(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/stocks/'.$this->stock->id)->assertForbidden();
    }

    public function test_admin_gets_403_on_member_route(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/stocks/'.$this->stock->id)->assertForbidden();
    }

    public function test_component_forbidden_for_non_owner(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']));

        Livewire::test(StockDetail::class, ['stock' => $this->stock])->assertForbidden();
    }

    public function test_mount_fills_form(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->assertSet('form.name', $this->stock->name)
            ->assertSet('form.quantity', 10);
    }

    public function test_save_updates_stock(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->set('form.name', 'Nama Baru')
            ->set('form.quantity', 0)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('stocks', ['id' => $this->stock->id, 'name' => 'Nama Baru', 'status' => 'out_of_stock']);
    }

    public function test_save_validates(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    public function test_save_uploads_cover_and_replaces_old_one(): void
    {
        Storage::fake('public');

        $component = Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->set('cover', UploadedFile::fake()->image('a.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $first = $this->stock->fresh()->cover_image_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);

        $component->set('cover', UploadedFile::fake()->image('b.jpg'))->call('save');

        $second = $this->stock->fresh()->cover_image_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_adjust(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])->call('adjust', -4);

        $this->assertDatabaseHas('stocks', ['id' => $this->stock->id, 'quantity' => 6]);
    }
}
