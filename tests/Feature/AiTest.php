<?php

namespace Tests\Feature;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiTest extends TestCase
{
    public function test_dto_from_and_to_array_with_defaults(): void
    {
        $item = GeneratedStockItem::fromArray(['name' => 'Pensil', 'quantity' => '10']);

        $this->assertSame('Pensil', $item->name);
        $this->assertNull($item->sku);
        $this->assertSame(10, $item->quantity);
        $this->assertSame('pcs', $item->unit);
        $this->assertNull($item->price);
        $this->assertSame(5, $item->minStock);
        $this->assertNull($item->description);
        $this->assertSame([
            'name' => 'Pensil', 'sku' => null, 'quantity' => 10, 'unit' => 'pcs',
            'price' => null, 'min_stock' => 5, 'description' => null,
        ], $item->toArray());
    }

    public function test_dto_from_array_with_all_fields(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => 'Buku', 'sku' => 'BK-1', 'quantity' => 3, 'unit' => 'box',
            'price' => '2500.5', 'min_stock' => 2, 'description' => 'Tebal',
        ]);

        $this->assertSame(2500.5, $item->price);
        $this->assertSame('box', $item->unit);
        $this->assertSame('BK-1', $item->sku);
    }

    public function test_generate_requires_api_key(): void
    {
        config(['openai.api_key' => null]);

        $this->expectException(\RuntimeException::class);

        (new OpenAiStockGeneratorService())->generate('apa saja');
    }

    public function test_generate_parses_items(): void
    {
        config(['openai.api_key' => 'test-key', 'openai.model' => 'gpt-test']);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['items' => [
                    ['name' => 'A', 'quantity' => 1],
                    ['name' => 'B', 'quantity' => 2, 'min_stock' => 1],
                ]])]]],
            ]),
        ]);

        $items = (new OpenAiStockGeneratorService())->generate('buat dua item');

        $this->assertCount(2, $items);
        $this->assertInstanceOf(GeneratedStockItem::class, $items[0]);
        $this->assertSame('B', $items[1]->name);
    }

    public function test_generate_handles_missing_items_and_content(): void
    {
        config(['openai.api_key' => 'test-key']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => '{}']]]])]);
        $this->assertSame([], (new OpenAiStockGeneratorService())->generate('x'));

        Http::fake(['api.openai.com/*' => Http::response([])]);
        $this->assertSame([], (new OpenAiStockGeneratorService())->generate('x'));
    }

    public function test_generate_throws_on_http_error(): void
    {
        config(['openai.api_key' => 'test-key']);
        Http::fake(['api.openai.com/*' => Http::response([], 500)]);

        $this->expectException(RequestException::class);

        (new OpenAiStockGeneratorService())->generate('x');
    }
}
