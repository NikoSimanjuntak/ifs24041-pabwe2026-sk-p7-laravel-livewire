<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    public function test_throws_when_api_key_missing(): void
    {
        config(['openai.api_key' => null]);

        $this->expectException(\RuntimeException::class);

        (new OpenAiStockGeneratorService())->generate('apa saja');
    }

    public function test_returns_generated_items(): void
    {
        config(['openai.api_key' => 'test-key', 'openai.model' => 'gpt-test']);

        $content = json_encode(['items' => [
            ['name' => 'Laptop', 'sku' => 'LAP', 'quantity' => 5, 'unit' => 'unit', 'price' => 1000, 'min_stock' => 2, 'description' => 'x'],
        ]]);

        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => $content]]]])]);

        $items = (new OpenAiStockGeneratorService())->generate('buat laptop');

        $this->assertCount(1, $items);
        $this->assertInstanceOf(GeneratedStockItem::class, $items[0]);
        $this->assertSame('Laptop', $items[0]->name);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key'));
    }

    public function test_returns_empty_array_when_response_has_no_content(): void
    {
        config(['openai.api_key' => 'test-key']);

        Http::fake(['api.openai.com/*' => Http::response([])]);

        $this->assertSame([], (new OpenAiStockGeneratorService())->generate('kosong'));
    }

    public function test_throws_on_http_error(): void
    {
        config(['openai.api_key' => 'test-key']);

        Http::fake(['api.openai.com/*' => Http::response([], 500)]);

        $this->expectException(RequestException::class);

        (new OpenAiStockGeneratorService())->generate('gagal');
    }

    public function test_throws_on_invalid_json_content(): void
    {
        config(['openai.api_key' => 'test-key']);

        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'bukan json']]]])]);

        $this->expectException(\JsonException::class);

        (new OpenAiStockGeneratorService())->generate('rusak');
    }
}
