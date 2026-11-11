<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    public function test_global_rate_limiter_is_registered_for_guest_and_user(): void
    {
        $limiter = RateLimiter::limiter('global');
        $this->assertNotNull($limiter);

        $guest = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);
        $this->assertSame('10.0.0.1', $limiter($guest)->key);

        $user = \App\Models\User::factory()->make(['id' => 7]);
        $withUser = Request::create('/');
        $withUser->setUserResolver(fn () => $user);
        $this->assertSame(7, $limiter($withUser)->key);
    }

    public function test_boot_forces_https_when_configured(): void
    {
        config(['app.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/x'));
    }
}
