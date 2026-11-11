<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsAndPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_stock_relations(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->stocks->contains($stock));
        $this->assertTrue($stock->user->is($user));
    }

    public function test_user_is_admin(): void
    {
        $this->assertTrue(User::factory()->create(['role' => 'admin'])->isAdmin());
        $this->assertFalse(User::factory()->create()->isAdmin());
    }

    public function test_sync_status(): void
    {
        $stock = new Stock(['quantity' => 0, 'min_stock' => 5]);
        $stock->syncStatus();
        $this->assertSame(StockStatus::OUT_OF_STOCK, $stock->status);
    }

    public function test_policy_rules(): void
    {
        $policy = new StockPolicy();
        $member = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = Stock::factory()->create(['user_id' => $member->id]);

        $this->assertTrue($policy->view($member, $stock));
        $this->assertTrue($policy->view($admin, $stock));
        $this->assertFalse($policy->view($other, $stock));

        $this->assertTrue($policy->create($member));
        $this->assertFalse($policy->create($admin));

        $this->assertTrue($policy->update($member, $stock));
        $this->assertFalse($policy->update($other, $stock));
        $this->assertFalse($policy->update($admin, $stock));

        $this->assertTrue($policy->delete($member, $stock));
        $this->assertFalse($policy->delete($other, $stock));
    }
}
