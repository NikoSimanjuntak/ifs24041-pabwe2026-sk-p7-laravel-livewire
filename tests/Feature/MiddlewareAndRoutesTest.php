<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MiddlewareAndRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/stocks');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/')->assertRedirect('/admin/users');
    }

    public function test_pages_render_for_roles(): void
    {
        $member = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $member->id]);

        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();

        $this->actingAs($member);
        $this->get('/stocks')->assertOk();
        $this->get('/stocks/'.$stock->id)->assertOk();
        $this->get('/profile')->assertOk();
    }

    public function test_admin_pages_and_role_protection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/users/'.$member->id.'/stocks')->assertOk();
        $this->actingAs($admin)->get('/stocks')->assertForbidden();
        $this->actingAs($member)->get('/admin/users')->assertForbidden();
    }

    public function test_stock_detail_is_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)->get('/stocks/'.$stock->id)->assertForbidden();
    }

    public function test_logout_invalidates_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/stocks')->assertRedirect('/login');
    }

    public function test_ensure_role_middleware_directly(): void
    {
        $middleware = new EnsureRole();
        $next = fn () => response('ok');

        $request = Request::create('/');
        $request->setUserResolver(fn () => User::factory()->make(['role' => 'admin']));
        $this->assertSame('ok', $middleware->handle($request, $next, 'admin')->getContent());

        $this->expectException(HttpException::class);
        $guest = Request::create('/');
        $middleware->handle($guest, $next, 'admin');
    }

    public function test_ensure_stock_ownership_middleware_directly(): void
    {
        $middleware = new EnsureStockOwnership();
        $next = fn () => response('ok');
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => new class($stock) {
            public function __construct(private $s) {}
            public function parameter($k, $d = null) { return $this->s; }
        });
        $this->assertSame('ok', $middleware->handle($request, $next)->getContent());

        $bad = Request::create('/');
        $bad->setUserResolver(fn () => $user);
        $bad->setRouteResolver(fn () => new class {
            public function parameter($k, $d = null) { return null; }
        });
        $this->expectException(HttpException::class);
        $middleware->handle($bad, $next);
    }
}
