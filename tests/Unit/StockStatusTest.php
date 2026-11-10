<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
use App\Features\Stock\Enums\StockStatus;
class StockStatusTest extends TestCase {
 public function test_quantity_maps_to_expected_status(): void {
  self::assertSame(StockStatus::OUT_OF_STOCK,StockStatus::fromQuantity(0,5));
  self::assertSame(StockStatus::LOW_STOCK,StockStatus::fromQuantity(5,5));
  self::assertSame(StockStatus::AVAILABLE,StockStatus::fromQuantity(6,5));
 }
}
