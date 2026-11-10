<?php
namespace App\Features\Admin\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Features\Admin\Services\AdminUserService;
class AdminUserStockList extends Component {
    use WithPagination;
    public User $user;
    public function mount(User $user){ abort_unless($user->role==='member',404); }
    public function render(AdminUserService $service){ return view('livewire.admin.admin-user-stock-list',['stocks'=>$service->stocks($this->user)])->layout('components.layouts.app'); }
}
