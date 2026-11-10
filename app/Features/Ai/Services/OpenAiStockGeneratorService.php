<?php
namespace App\Features\Ai\Services;
use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Support\Facades\Http;
class OpenAiStockGeneratorService implements AiClientInterface {
    public function generate(string $prompt): array {
        $key=config('openai.api_key');
        if(!$key) throw new \RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        $response=Http::withToken($key)->timeout(30)->post('https://api.openai.com/v1/chat/completions',[
            'model'=>config('openai.model'),'response_format'=>['type'=>'json_object'],
            'messages'=>[['role'=>'system','content'=>'Buat JSON object dengan key items berisi array item stok. Setiap item: name, sku, quantity, unit, price, min_stock, description.'],['role'=>'user','content'=>$prompt]]
        ])->throw()->json();
        $content=$response['choices'][0]['message']['content']??'{}';
        $decoded=json_decode($content,true,512,JSON_THROW_ON_ERROR);
        return array_map(fn($item)=>GeneratedStockItem::fromArray($item),$decoded['items']??[]);
    }
}
