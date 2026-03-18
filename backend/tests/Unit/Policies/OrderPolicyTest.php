<?php

namespace Tests\Unit\Policies;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for OrderPolicy.
 */
class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrderPolicy();
    }

    /**
     * viewAny returns true for any user.
     */
    public function test_view_any_returns_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    /**
     * view returns true when user owns the order.
     */
    public function test_view_returns_true_when_owner(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $order));
    }

    /**
     * view returns false when user does not own the order.
     */
    public function test_view_returns_false_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->view($user, $order));
    }

    /**
     * create returns true for any user.
     */
    public function test_create_returns_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    /**
     * update returns true for admin.
     */
    public function test_update_returns_true_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $order = Order::factory()->create();

        $this->assertTrue($this->policy->update($admin, $order));
    }

    /**
     * update returns false for regular user.
     */
    public function test_update_returns_false_for_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->update($user, $order));
    }

    /**
     * delete returns true for admin.
     */
    public function test_delete_returns_true_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $order = Order::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $order));
    }

    /**
     * delete returns false for regular user.
     */
    public function test_delete_returns_false_for_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->delete($user, $order));
    }
}
