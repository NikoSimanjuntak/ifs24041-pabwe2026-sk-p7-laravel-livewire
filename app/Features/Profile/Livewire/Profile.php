<?php
namespace App\Features\Profile\Livewire;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Features\Profile\Services\ProfileService;
class Profile extends Component {
    use WithFileUploads;
    public array $form=[]; public $photo;
    public function mount(){ $this->form=auth()->user()->only(['name','username','email']); }
    public function save(ProfileService $service){
        $data=$this->validate(['form.name'=>'required|string|max:255','form.username'=>'required|string|max:50|unique:users,username,'.auth()->id(),'form.email'=>'required|email|unique:users,email,'.auth()->id(),'form.password'=>'nullable|string|min:8']);
        $service->update(auth()->user(),$data['form']);
        if($this->photo){ $this->validate(['photo'=>'image|max:2048']); $service->photo(auth()->user(),$this->photo); }
        $this->dispatch('notify',message:'Profil berhasil diperbarui.');
    }
    public function render(){ return view('livewire.profile.profile')->layout('components.layouts.app'); }
}
