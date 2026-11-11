<?php

namespace Tests\Unit;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use Tests\TestCase;

class StockPolicyTest extends TestCase
{
    private function user(int $id, string $role): User
    {
        $user = new User(['role' => $role]);
        $user->id = $id;

        return $user;
    }

    private function stock(int $ownerId): Stock
    {
        $stock = new Stock();
        $stock->user_id = $ownerId;

        return $stock;
    }

    public function test_view(): void
    {
        $policy = new StockPolicy();

        $this->assertTrue($policy->view($this->user(1, 'admin'), $this->stock(2)));
        $this->assertTrue($policy->view($this->user(1, 'member'), $this->stock(1)));
        $this->assertFalse($policy->view($this->user(1, 'member'), $this->stock(2)));
    }

    public function test_create(): void
    {
        $policy = new StockPolicy();

        $this->assertTrue($policy->create($this->user(1, 'member')));
        $this->assertFalse($policy->create($this->user(1, 'admin')));
    }

    public function test_update_and_delete(): void
    {
        $policy = new StockPolicy();

        $this->assertTrue($policy->update($this->user(1, 'member'), $this->stock(1)));
        $this->assertFalse($policy->update($this->user(1, 'member'), $this->stock(2)));
        $this->assertFalse($policy->update($this->user(1, 'admin'), $this->stock(1)));

        $this->assertTrue($policy->delete($this->user(1, 'member'), $this->stock(1)));
        $this->assertFalse($policy->delete($this->user(1, 'member'), $this->stock(2)));
    }
}
