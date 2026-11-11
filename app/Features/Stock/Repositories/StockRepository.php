<?php
namespace App\Features\Stock\Repositories;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class StockRepository implements StockRepositoryInterface {
    public function paginateFor(User $user, string $search, string $status, int $perPage=10): LengthAwarePaginator {
        return Stock::where('user_id',$user->id)->when($search,fn($q)=>$q->where(fn($s)=>$s->where('name','like',"%$search%")->orWhere('sku','like',"%$search%")))
            ->when($status,fn($q)=>$q->where('status',$status))->latest()->paginate($perPage);
    }
    public function createFor(User $user,array $data): Stock {
        $stock = $user->stocks()->make($data); $stock->syncStatus(); $stock->save(); return $stock;
    }
    public function update(Stock $stock,array $data): Stock { $stock->fill($data); $stock->syncStatus(); $stock->save(); return $stock; }
    public function delete(Stock $stock): void { $stock->delete(); }
}
