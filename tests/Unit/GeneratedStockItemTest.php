<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_from_array_with_all_fields(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => 'Laptop', 'sku' => 'LAP', 'quantity' => '5', 'unit' => 'unit',
            'price' => '1000.5', 'min_stock' => '2', 'description' => 'Bagus',
        ]);

        $this->assertSame(
            ['name' => 'Laptop', 'sku' => 'LAP', 'quantity' => 5, 'unit' => 'unit', 'price' => 1000.5, 'min_stock' => 2, 'description' => 'Bagus'],
            $item->toArray()
        );
    }

    public function test_from_array_uses_defaults(): void
    {
        $item = GeneratedStockItem::fromArray(['name' => 'Kabel', 'quantity' => 1]);

        $this->assertSame(
            ['name' => 'Kabel', 'sku' => null, 'quantity' => 1, 'unit' => 'pcs', 'price' => null, 'min_stock' => 5, 'description' => null],
            $item->toArray()
        );
    }
}
