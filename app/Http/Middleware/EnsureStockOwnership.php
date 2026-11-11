<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Features\Stock\Models\Stock;
use Symfony\Component\HttpFoundation\Response;
class EnsureStockOwnership {
    public function handle(Request $request, Closure $next): Response {
        $stock = $request->route('stock');
        abort_unless($stock instanceof Stock && $stock->user_id === $request->user()->id, 403);
        return $next($request);
    }
}
