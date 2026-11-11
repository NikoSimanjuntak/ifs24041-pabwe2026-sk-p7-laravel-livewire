<?php

namespace Tests\Feature;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_filters_by_owner_search_and_status(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Laptop', 'sku' => 'LP-1', 'quantity' => 50, 'min_stock' => 5, 'status' => StockStatus::AVAILABLE]);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Mouse', 'sku' => 'MS-1', 'quantity' => 0, 'min_stock' => 5, 'status' => StockStatus::OUT_OF_STOCK]);
        Stock::factory()->create(['user_id' => $other->id, 'name' => 'Laptop Lain', 'sku' => 'LP-2']);

        $repo = new StockRepository();

        $this->assertSame(2, $repo->paginateFor($user, '', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, 'Laptop', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, 'MS-1', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, '', 'out_of_stock')->total());
        $this->assertSame(0, $repo->paginateFor($user, 'Laptop', 'out_of_stock')->total());
    }

    public function test_create_update_and_delete(): void
    {
        $user = User::factory()->create();
        $repo = new StockRepository();

        $stock = $repo->createFor($user, [
            'name' => 'Kabel', 'quantity' => 3, 'min_stock' => 5, 'unit' => 'pcs',
        ]);

        $this->assertSame(StockStatus::LOW_STOCK, $stock->status);
        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'user_id' => $user->id]);

        $updated = $repo->update($stock, ['quantity' => 0]);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $updated->status);

        $repo->delete($stock);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
