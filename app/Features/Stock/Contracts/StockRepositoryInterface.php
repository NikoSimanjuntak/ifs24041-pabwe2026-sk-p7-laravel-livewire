<?php
namespace App\Features\Stock\Contracts;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface StockRepositoryInterface {
    public function paginateFor(User $user, string $search, string $status, int $perPage = 10): LengthAwarePaginator;
    public function createFor(User $user, array $data): Stock;
    public function update(Stock $stock, array $data): Stock;
    public function delete(Stock $stock): void;
}
