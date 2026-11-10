<?php

namespace App\Features\Stock\Services;

use App\Models\User;
use App\Features\Stock\Enums\StockStatus;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class StockExcelService
{
    public function export(User $user): string
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();

        $sheet->fromArray([
            ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status', 'description']
        ]);

        $row = 2;

        foreach ($user->stocks()->orderBy('id')->get() as $stock) {
            $sheet->fromArray([
                [
                    $stock->name,
                    $stock->sku,
                    $stock->quantity,
                    $stock->unit,
                    $stock->price,
                    $stock->min_stock,
                    $stock->status->value,
                    $stock->description
                ]
            ], null, "A{$row}");

            $row++;
        }

        $path = 'exports/stocks-' . $user->id . '-' . now()->format('YmdHis') . '.xlsx';

        Storage::disk('local')->makeDirectory('exports');

        (new Xlsx($book))->save(Storage::disk('local')->path($path));

        return $path;
    }

    public function import(User $user, string $path): int
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray();

        array_shift($rows);

        $count = 0;

        foreach ($rows as $row) {
            if (empty($row[0])) {
                continue;
            }

            $quantity = max(0, (int) ($row[2] ?? 0));
            $min = max(0, (int) ($row[5] ?? 5));

            $user->stocks()->create([
                'name' => $row[0],
                'sku' => $row[1] ?: null,
                'quantity' => $quantity,
                'unit' => $row[3] ?: 'pcs',
                'price' => $row[4] ?: null,
                'min_stock' => $min,
                'status' => StockStatus::fromQuantity($quantity, $min),
                'description' => $row[7] ?? null,
            ]);

            $count++;
        }

        return $count;
    }
}
