<?php

namespace Tests\Unit;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Profile\Services\ProfileService;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelsAndRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_helpers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        Stock::factory()->create(['user_id' => $member->id]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($member->isAdmin());
        $this->assertCount(1, $member->stocks);
    }

    public function test_stock_relation_and_status_sync(): void
    {
        $stock = Stock::factory()->create(['quantity' => 3, 'min_stock' => 5]);

        $this->assertInstanceOf(User::class, $stock->user);

        $stock->quantity = 0;
        $stock->syncStatus();
        $this->assertSame(StockStatus::OUT_OF_STOCK, $stock->status);

        $stock->quantity = 50;
        $stock->syncStatus();
        $this->assertSame(StockStatus::AVAILABLE, $stock->status);
    }

    public function test_repository_crud_and_filters(): void
    {
        $user = User::factory()->create();
        $repo = new StockRepository();

        $created = $repo->createFor($user, ['name' => 'Laptop', 'sku' => 'LAP-1', 'quantity' => 2, 'unit' => 'pcs', 'min_stock' => 5]);
        $this->assertSame(StockStatus::LOW_STOCK, $created->status);

        $repo->createFor($user, ['name' => 'Mouse', 'sku' => 'MOU-1', 'quantity' => 50, 'unit' => 'pcs', 'min_stock' => 5]);

        $this->assertSame(2, $repo->paginateFor($user, '', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, 'Laptop', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, 'MOU', '')->total());
        $this->assertSame(1, $repo->paginateFor($user, '', 'low_stock')->total());

        $updated = $repo->update($created, ['quantity' => 0]);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $updated->status);

        $repo->delete($created);
        $this->assertDatabaseMissing('stocks', ['id' => $created->id]);
    }

    public function test_repository_is_bound_in_container(): void
    {
        $this->assertInstanceOf(StockRepository::class, app(StockRepositoryInterface::class));
    }

    public function test_profile_service(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Lama']);
        $service = new ProfileService();

        $service->update($user, ['name' => 'Baru']);
        $this->assertSame('Baru', $user->fresh()->name);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $service->update($user, ['name' => 'Baru', 'password' => 'rahasia-baru']);
        $this->assertTrue(Hash::check('rahasia-baru', $user->fresh()->password));

        $service->photo($user, UploadedFile::fake()->image('a.jpg'));
        $first = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $service->photo($user->fresh(), UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);
    }

    public function test_admin_user_service(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        User::factory()->create(['role' => 'admin']);
        Stock::factory()->count(2)->create(['user_id' => $member->id]);

        $service = new AdminUserService();

        $users = $service->users();
        $this->assertSame(1, $users->total());
        $this->assertSame(2, $users->first()->stocks_count);
        $this->assertSame(2, $service->stocks($member)->total());
    }

    public function test_provider_registers_global_rate_limiter_and_forces_https(): void
    {
        config(['app.force_https' => true]);

        $provider = new \App\Providers\AppServiceProvider($this->app);
        $provider->boot();

        $this->assertNotNull(\Illuminate\Support\Facades\RateLimiter::limiter('global'));
        $this->assertStringStartsWith('https://', url('/'));

        $request = \Illuminate\Http\Request::create('/');
        $limit = \Illuminate\Support\Facades\RateLimiter::limiter('global')($request);
        $this->assertSame(1000, $limit->maxAttempts);
    }

    public function test_ensure_stock_ownership_rejects_non_model_parameter(): void
    {
        $user = User::factory()->create();
        $request = \Illuminate\Http\Request::create('/stocks/abc');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($request) {
            $route = new \Illuminate\Routing\Route('GET', '/stocks/{stock}', []);
            $route->bind($request);
            $route->setParameter('stock', 'abc');

            return $route;
        });

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new \App\Http\Middleware\EnsureStockOwnership())->handle($request, fn () => response('ok'));
    }

    public function test_ensure_role_rejects_guest(): void
    {
        $request = \Illuminate\Http\Request::create('/x');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new \App\Http\Middleware\EnsureRole())->handle($request, fn () => response('ok'), 'admin');
    }
}
