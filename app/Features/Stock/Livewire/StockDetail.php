<?php
namespace App\Features\Stock\Livewire;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
class StockDetail extends Component {
    use WithFileUploads, AuthorizesRequests;
    public Stock $stock; public $cover; public array $form=[];
    public function mount(Stock $stock){ $this->authorize('view',$stock); $this->stock=$stock; $this->form=$stock->only(['name','sku','description','quantity','unit','price','min_stock']); }
    public function save(StockService $service){
        $this->authorize('update',$this->stock);
        $data=$this->validate(['form.name'=>'required|string|max:255','form.sku'=>'nullable|string|max:100','form.description'=>'nullable|string','form.quantity'=>'required|integer|min:0','form.unit'=>'required|string|max:30','form.price'=>'nullable|numeric|min:0','form.min_stock'=>'required|integer|min:0','cover'=>'nullable|image|max:2048']);
        $this->stock=$service->update($this->stock,$data['form']);
        if($this->cover){ if($this->stock->cover_image_path) Storage::disk('public')->delete($this->stock->cover_image_path); $this->stock->cover_image_path=$this->cover->store('stock-covers','public'); $this->stock->save(); }
        $this->dispatch('notify',message:'Detail stok diperbarui.');
    }
    public function adjust(int $delta,StockService $service){ $this->authorize('update',$this->stock); $this->stock=$service->adjust($this->stock,$delta); }
    public function render(){ return view('livewire.stock.stock-detail')->layout('components.layouts.app'); }
}
