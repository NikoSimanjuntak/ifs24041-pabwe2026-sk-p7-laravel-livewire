<?php
namespace Database\Factories;
use App\Features\Stock\Models\Stock; use App\Features\Stock\Enums\StockStatus; use App\Models\User; use Illuminate\Database\Eloquent\Factories\Factory;
class StockFactory extends Factory {
    protected $model=Stock::class;
    public function definition(): array { $qty=fake()->numberBetween(0,100); $min=5; return ['user_id'=>User::factory(),'name'=>fake()->words(2,true),'sku'=>fake()->unique()->bothify('SKU-####'),'description'=>fake()->sentence(),'quantity'=>$qty,'unit'=>'pcs','price'=>fake()->randomFloat(2,1000,1000000),'min_stock'=>$min,'status'=>StockStatus::fromQuantity($qty,$min)]; }
}
