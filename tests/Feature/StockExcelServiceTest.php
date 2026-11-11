<?php

namespace Tests\Feature;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_writes_header_and_rows(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Stock::factory()->create([
            'user_id' => $user->id, 'name' => 'Laptop', 'sku' => 'LP-1', 'quantity' => 7,
            'min_stock' => 5, 'unit' => 'pcs', 'price' => 1000, 'description' => 'Bagus',
            'status' => StockStatus::AVAILABLE,
        ]);

        $path = (new StockExcelService())->export($user);

        Storage::disk('local')->assertExists($path);
        $rows = IOFactory::load(Storage::disk('local')->path($path))->getActiveSheet()->toArray();

        $this->assertSame('name', $rows[0][0]);
        $this->assertSame('Laptop', $rows[1][0]);
        $this->assertSame('available', $rows[1][6]);
    }

    public function test_import_creates_stocks_and_skips_empty_rows(): void
    {
        $user = User::factory()->create();

        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([
            ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status', 'description'],
            ['Laptop', 'LP-1', 10, 'unit', 5000, 3, '', 'Catatan'],
            ['', 'X', 1, 'pcs', 1, 1, '', ''],
            ['Pensil', '', -4, '', '', '', '', ''],
        ]);
        $file = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($book))->save($file);

        $count = (new StockExcelService())->import($user, $file);
        @unlink($file);

        $this->assertSame(2, $count);
        $this->assertDatabaseHas('stocks', ['name' => 'Laptop', 'sku' => 'LP-1', 'unit' => 'unit', 'status' => 'available']);
        $this->assertDatabaseHas('stocks', ['name' => 'Pensil', 'sku' => null, 'unit' => 'pcs', 'quantity' => 0, 'status' => 'out_of_stock']);
    }
}
