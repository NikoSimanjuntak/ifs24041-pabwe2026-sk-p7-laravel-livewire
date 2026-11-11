<?php

namespace Tests\Unit;

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

    public function test_export_writes_xlsx_with_user_stocks(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Laptop']);
        Stock::factory()->create(['name' => 'Bukan Milik User']);

        $path = (new StockExcelService())->export($user);

        $this->assertStringStartsWith('exports/stocks-'.$user->id.'-', $path);
        Storage::disk('local')->assertExists($path);

        $rows = IOFactory::load(Storage::disk('local')->path($path))->getActiveSheet()->toArray();

        $this->assertSame('name', $rows[0][0]);
        $this->assertSame('Laptop', $rows[1][0]);
        $this->assertCount(2, $rows);
    }

    public function test_import_creates_stocks_and_skips_empty_names(): void
    {
        $user = User::factory()->create();

        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([
            ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status', 'description'],
            ['Laptop', 'LAP-1', 10, 'unit', 7500000, 5, '', 'Deskripsi'],
            ['', 'KOSONG', 1, 'pcs', 1, 1, '', ''],
            ['Mouse', '', -3, '', '', '', '', null],
        ]);

        $file = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($book))->save($file);

        $count = (new StockExcelService())->import($user, $file);
        unlink($file);

        $this->assertSame(2, $count);
        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'Laptop', 'sku' => 'LAP-1', 'quantity' => 10, 'unit' => 'unit', 'status' => 'available']);
        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'Mouse', 'sku' => null, 'quantity' => 0, 'unit' => 'pcs', 'status' => 'out_of_stock']);
    }
}
