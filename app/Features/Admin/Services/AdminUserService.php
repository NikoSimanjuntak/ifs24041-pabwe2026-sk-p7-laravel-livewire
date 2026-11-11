<?php
namespace App\Features\Admin\Services;
use App\Models\User;
class AdminUserService {
    public function users(){ return User::where('role','member')->withCount('stocks')->latest()->paginate(15); }
    public function stocks(User $user){ return $user->stocks()->latest()->paginate(15); }
}
