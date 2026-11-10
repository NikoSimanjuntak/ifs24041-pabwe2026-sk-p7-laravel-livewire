<?php
namespace App\Providers;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Repositories\StockRepository;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void { $this->app->bind(StockRepositoryInterface::class, StockRepository::class); }
    public function boot(): void {
        RateLimiter::for('global', fn ($request) => Limit::perSecond(1000)->by($request->user()?->id ?: $request->ip()));
        if (config('app.force_https', env('FORCE_HTTPS', false))) \URL::forceScheme('https');
    }
}
