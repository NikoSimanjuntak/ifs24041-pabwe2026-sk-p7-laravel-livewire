<?php
namespace App\Features\Stock\Services;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class StockService {
    public function __construct(private StockRepositoryInterface $stocks) {}
    public function list(User $user,string $search='',string $status='') { return $this->stocks->paginateFor($user,$search,$status); }
    public function create(User $user,array $data): Stock { return DB::transaction(fn()=> $this->stocks->createFor($user,$data)); }
    public function update(Stock $stock,array $data): Stock { return DB::transaction(fn()=> $this->stocks->update($stock,$data)); }
    public function adjust(Stock $stock,int $delta): Stock {
        return DB::transaction(function() use($stock,$delta) {
            $stock = Stock::whereKey($stock->id)->lockForUpdate()->firstOrFail();
            if ($stock->quantity + $delta < 0) throw ValidationException::withMessages(['quantity'=>'Jumlah stok tidak boleh kurang dari nol.']);
            $stock->quantity += $delta; $stock->syncStatus(); $stock->save(); return $stock;
        });
    }
    public function delete(Stock $stock): void { $this->stocks->delete($stock); }
}
