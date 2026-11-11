<?php
namespace App\Features\Profile\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class ProfileService {
    public function update(User $user,array $data): User { $user->fill(collect($data)->except(['password'])->all()); if(!empty($data['password'])) $user->password=Hash::make($data['password']); $user->save(); return $user; }
    public function photo(User $user,$file): User { if($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path); $user->profile_photo_path=$file->store('profile-photos','public'); $user->save(); return $user; }
}
