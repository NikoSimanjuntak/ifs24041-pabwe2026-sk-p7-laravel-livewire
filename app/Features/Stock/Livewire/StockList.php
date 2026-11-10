<?php
namespace App\Features\Stock\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Features\Stock\Services\StockService;
use App\Features\Stock\Enums\StockStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class StockList extends Component {
    use WithPagination, AuthorizesRequests;
    public string $search=''; public string $status=''; public bool $showModal=false;
    public ?int $editingId=null;
    public array $form=['name'=>'','sku'=>'','description'=>'','quantity'=>0,'unit'=>'pcs','price'=>null,'min_stock'=>5];
    protected function rules(): array { return ['form.name'=>'required|string|max:255','form.sku'=>'nullable|string|max:100','form.description'=>'nullable|string','form.quantity'=>'required|integer|min:0','form.unit'=>'required|string|max:30','form.price'=>'nullable|numeric|min:0','form.min_stock'=>'required|integer|min:0']; }
    public function updatedSearch(){ $this->resetPage(); }
    public function updatedStatus(){ $this->resetPage(); }
    public function create(){ $this->resetValidation(); $this->editingId=null; $this->form=['name'=>'','sku'=>'','description'=>'','quantity'=>0,'unit'=>'pcs','price'=>null,'min_stock'=>5]; $this->showModal=true; }
    public function edit(int $id){ $stock=auth()->user()->stocks()->findOrFail($id); $this->authorize('update',$stock); $this->editingId=$id; $this->form=$stock->only(array_keys($this->form)); $this->showModal=true; }
    public function save(StockService $service){
        $data=$this->validate()['form'];
        if($this->editingId){ $stock=auth()->user()->stocks()->findOrFail($this->editingId); $this->authorize('update',$stock); $service->update($stock,$data); }
        else { $this->authorize('create', \App\Features\Stock\Models\Stock::class); $service->create(auth()->user(),$data); }
        $this->showModal=false; $this->dispatch('notify',message:'Data stok berhasil disimpan.');
    }
    public function adjust(int $id,int $delta,StockService $service){ $stock=auth()->user()->stocks()->findOrFail($id); $this->authorize('update',$stock); $service->adjust($stock,$delta); }
    public function delete(int $id,StockService $service){ $stock=auth()->user()->stocks()->findOrFail($id); $this->authorize('delete',$stock); $service->delete($stock); }
    public function render(StockService $service){ return view('livewire.stock.stock-list',['stocks'=>$service->list(auth()->user(),$this->search,$this->status),'statuses'=>StockStatus::cases()])->layout('components.layouts.app'); }
}
