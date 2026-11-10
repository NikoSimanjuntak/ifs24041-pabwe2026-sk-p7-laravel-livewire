<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use App\Models\User; use App\Features\Stock\Models\Stock; use App\Features\Stock\Enums\StockStatus;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        User::updateOrCreate(['username'=>'admin'],['name'=>'Administrator','email'=>'admin@example.test','password'=>'password','role'=>'admin']);
        $member=User::updateOrCreate(['username'=>'member'],['name'=>'Member Demo','email'=>'member@example.test','password'=>'password','role'=>'member']);
        if(!$member->stocks()->exists()) foreach([['Laptop Lenovo','SKU-LAP-001',12,5,7500000],['Mouse Wireless','SKU-MOU-002',3,5,125000],['Keyboard','SKU-KEY-003',0,5,250000]] as [$name,$sku,$qty,$min,$price]) $member->stocks()->create(['name'=>$name,'sku'=>$sku,'quantity'=>$qty,'min_stock'=>$min,'price'=>$price,'unit'=>'pcs','status'=>StockStatus::fromQuantity($qty,$min)]);
    }
}
