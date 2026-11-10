<?php

namespace Tests\Unit;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function test_quantity_maps_to_expected_status(): void
    {
        self::assertSame(
            StockStatus::OUT_OF_STOCK,
            StockStatus::fromQuantity(0, 5)
        );

        self::assertSame(
            StockStatus::LOW_STOCK,
            StockStatus::fromQuantity(5, 5)
        );

        self::assertSame(
            StockStatus::AVAILABLE,
            StockStatus::fromQuantity(6, 5)
        );
    }

    public function test_status_has_correct_label(): void
    {
        self::assertSame('Tersedia', StockStatus::AVAILABLE->label());
        self::assertSame('Stok Menipis', StockStatus::LOW_STOCK->label());
        self::assertSame('Habis', StockStatus::OUT_OF_STOCK->label());
    }

    public function test_status_has_correct_badge_class(): void
    {
        self::assertSame(
            'bg-green-100 text-green-800',
            StockStatus::AVAILABLE->badgeClass()
        );

        self::assertSame(
            'bg-yellow-100 text-yellow-800',
            StockStatus::LOW_STOCK->badgeClass()
        );

        self::assertSame(
            'bg-red-100 text-red-800',
            StockStatus::OUT_OF_STOCK->badgeClass()
        );
    }

    public function test_negative_quantity_is_out_of_stock(): void
    {
        self::assertSame(
            StockStatus::OUT_OF_STOCK,
            StockStatus::fromQuantity(-1, 5)
        );
    }

    public function test_zero_minimum_stock_boundary(): void
    {
        self::assertSame(
            StockStatus::OUT_OF_STOCK,
            StockStatus::fromQuantity(0, 0)
        );

        self::assertSame(
            StockStatus::AVAILABLE,
            StockStatus::fromQuantity(1, 0)
        );
    }
}
