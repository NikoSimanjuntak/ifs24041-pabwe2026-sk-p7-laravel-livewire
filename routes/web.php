
<?php

use App\Features\Auth\Livewire\Login;
use App\Features\Auth\Livewire\Register;
use App\Features\Profile\Livewire\Profile;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Admin\Livewire\AdminUserStockList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->guest()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'admin'
        ? redirect('/admin/users')
        : redirect('/stocks');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function () {
    auth()->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:member'])->group(function () {
    Route::get('/stocks', StockList::class)->name('stocks.index');

    Route::get('/stocks/{stock}', StockDetail::class)
        ->middleware('owns.stock')
        ->name('stocks.show');

    Route::get('/profile', Profile::class)->name('profile');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/users', UserList::class)->name('users');

        Route::get('/users/{user}/stocks', AdminUserStockList::class)
            ->name('users.stocks');
    });
