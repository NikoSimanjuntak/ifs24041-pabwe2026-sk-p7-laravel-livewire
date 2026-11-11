<?php
namespace App\Features\Ai\DTO;
class GeneratedStockItem {
    public function __construct(public string $name,public ?string $sku,public int $quantity,public string $unit,public ?float $price,public int $minStock,public ?string $description=null) {}
    public static function fromArray(array $data): self { return new self($data['name'],$data['sku']??null,(int)$data['quantity'],$data['unit']??'pcs',isset($data['price'])?(float)$data['price']:null,(int)($data['min_stock']??5),$data['description']??null); }
    public function toArray(): array { return ['name'=>$this->name,'sku'=>$this->sku,'quantity'=>$this->quantity,'unit'=>$this->unit,'price'=>$this->price,'min_stock'=>$this->minStock,'description'=>$this->description]; }
}
