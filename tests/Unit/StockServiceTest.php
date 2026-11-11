<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Services\StockService;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_delegates_to_repository(): void
    {
        $user = new User();
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $repository = Mockery::mock(StockRepositoryInterface::class);
        $repository->shouldReceive('paginateFor')
            ->once()
            ->with($user, 'Laptop', 'available')
            ->andReturn($paginator);

        $service = new StockService($repository);

        $this->assertSame(
            $paginator,
            $service->list($user, 'Laptop', 'available')
        );
    }

    public function test_create_delegates_to_repository(): void
    {
        $user = new User();
        $stock = new Stock();

        $repository = Mockery::mock(StockRepositoryInterface::class);
        $repository->shouldReceive('createFor')
            ->once()
            ->with($user, ['name' => 'Laptop'])
            ->andReturn($stock);

        $service = new StockService($repository);

        $this->assertSame(
            $stock,
            $service->create($user, ['name' => 'Laptop'])
        );
    }

    public function test_update_delegates_to_repository(): void
    {
        $stock = new Stock();

        $repository = Mockery::mock(StockRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->with($stock, ['name' => 'Laptop Baru'])
            ->andReturn($stock);

        $service = new StockService($repository);

        $this->assertSame(
            $stock,
            $service->update($stock, ['name' => 'Laptop Baru'])
        );
    }

    public function test_delete_delegates_to_repository(): void
    {
        $stock = new Stock();

        $repository = Mockery::mock(StockRepositoryInterface::class);
        $repository->shouldReceive('delete')
            ->once()
            ->with($stock);

        $service = new StockService($repository);

        $service->delete($stock);

        $this->assertTrue(true);
    }

    public function test_adjust_increases_quantity_and_syncs_status(): void
    {
        $user = User::factory()->create();

        $stock = Stock::factory()->create([
            'user_id' => $user->id,
            'quantity' => 5,
            'min_stock' => 5,
        ]);

        $service = new StockService(
            Mockery::mock(StockRepositoryInterface::class)
        );

        $result = $service->adjust($stock, 3);

        $this->assertSame(8, $result->quantity);
        $this->assertSame(StockStatus::AVAILABLE, $result->status);
    }

    public function test_adjust_rejects_negative_quantity(): void
    {
        $user = User::factory()->create();

        $stock = Stock::factory()->create([
            'user_id' => $user->id,
            'quantity' => 2,
            'min_stock' => 5,
        ]);

        $service = new StockService(
            Mockery::mock(StockRepositoryInterface::class)
        );

        try {
            $service->adjust($stock, -3);
            $this->fail('Seharusnya terjadi ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'quantity',
                $exception->errors()
            );
        }

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 2,
        ]);
    }
}
