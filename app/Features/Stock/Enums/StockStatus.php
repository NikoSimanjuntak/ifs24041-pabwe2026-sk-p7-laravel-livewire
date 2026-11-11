<?php
namespace App\Features\Stock\Enums;
enum StockStatus: string
{
    case AVAILABLE = 'available';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    public function label(): string { return match($this) { self::AVAILABLE=>'Tersedia', self::LOW_STOCK=>'Stok Menipis', self::OUT_OF_STOCK=>'Habis' }; }
    public function badgeClass(): string { return match($this) { self::AVAILABLE=>'bg-green-100 text-green-800', self::LOW_STOCK=>'bg-yellow-100 text-yellow-800', self::OUT_OF_STOCK=>'bg-red-100 text-red-800' }; }
    public static function fromQuantity(int $quantity, int $minimum): self {
        return $quantity <= 0 ? self::OUT_OF_STOCK : ($quantity <= $minimum ? self::LOW_STOCK : self::AVAILABLE);
    }
}
