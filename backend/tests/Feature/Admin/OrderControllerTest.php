<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for OrderController. Covers index, update (delivery status), and destroy.
 * OrderController uses OrderPolicy for update/delete authorization.
 */
class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate as an admin user.
     */
    protected function asAdmin(): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Verifies that an authenticated admin can access the orders index page.
     */
    public function test_index_returns_200_with_orders_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Order::factory()->count(3)->create();

        $response = $this->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('orders');
        $this->assertCount(3, $response->viewData('orders'));
    }

    /**
     * Ensures unauthenticated users cannot access the orders list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for updating order delivery status.
     */
    public function test_update_sets_delivery_date_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $order = Order::factory()->create(['deliverd_at' => null]);
        $deliveryDate = Carbon::now()->addDays(2)->format('Y-m-d H:i');

        $response = $this->put(route('admin.orders.update', $order), [
            'deliverd_at' => $deliveryDate,
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('success', 'Order delivery status updated successfully');

        $order->refresh();
        $this->assertNotNull($order->deliverd_at);
    }

    /**
     * Tests updating order with null delivery date clears it.
     */
    public function test_update_clears_delivery_date_when_null(): void
    {
        $this->asAdmin();

        $order = Order::factory()->delivered()->create();

        $response = $this->put(route('admin.orders.update', $order), [
            'deliverd_at' => null,
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        $order->refresh();
        $this->assertNull($order->deliverd_at);
    }

    /**
     * Ensures guests cannot update orders.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $order = Order::factory()->create();

        $response = $this->put(route('admin.orders.update', $order), [
            'deliverd_at' => Carbon::now()->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for deleting an order.
     */
    public function test_destroy_deletes_order_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $order = Order::factory()->create();

        $response = $this->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('success', 'Order deleted successfully');
    }

    /**
     * Verifies that destroy actually removes the order from the database.
     */
    public function test_destroy_removes_order_from_database(): void
    {
        $this->asAdmin();

        $order = Order::factory()->create();

        $this->delete(route('admin.orders.destroy', $order));

        $this->assertNull(Order::find($order->id));
    }

    /**
     * Ensures guests cannot delete orders.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $order = Order::factory()->create();

        $response = $this->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
