<?php

namespace Tests\Feature\Api;

use App\Contracts\PaymentGateway;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Payments\CheckoutResult;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\OrderController. Covers index, show, store, and pay (checkout)
 * with PaymentGateway mocked so CI does not need live Stripe keys.
 */
class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Index returns authenticated user's orders.
     */
    public function test_index_returns_user_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('orders.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Orders retrieved successfully');
        $this->assertCount(2, $response->json('data.orders'));
    }

    /**
     * Index requires authentication.
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson(route('orders.index'));

        $response->assertStatus(401);
    }

    /**
     * Show returns order when user owns it.
     */
    public function test_show_returns_order_when_owner(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Order retrieved successfully');
    }

    /**
     * Show returns 403 when user does not own order.
     */
    public function test_show_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('orders.show', $order));

        $response->assertStatus(403);
    }

    /**
     * Store creates orders from cart items and clears cart.
     */
    public function test_store_creates_orders_from_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'qty' => 10]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('orders.store'), [
            'cartItems' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'price' => 1000,
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'Orders stored successfully');
        $this->assertDatabaseCount('orders', 1);
    }

    /**
     * Store requires authentication.
     */
    public function test_store_requires_authentication(): void
    {
        $product = Product::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();

        $response = $this->postJson(route('orders.store'), [
            'cartItems' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                    'price' => 1000,
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Store creates orders with coupon discount applied.
     */
    public function test_store_creates_orders_with_coupon_discount(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);
        $coupon = Coupon::factory()->create([
            'discount' => 20,
            'valid_until' => Carbon::now()->addDays(30),
        ]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('orders.store'), [
            'cartItems' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'price' => 1000,
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'coupon_id' => $coupon->id,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame($coupon->id, $order->coupon_id);
        $expectedTotal = 2000 - (2000 * 0.2);
        $this->assertEqualsWithDelta($expectedTotal, $order->total, 0.01);
    }

    /**
     * Show returns order with products and coupon (OrderResource full structure).
     */
    public function test_show_returns_order_with_products_and_coupon(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product', 'slug' => 'test-product']);
        $coupon = Coupon::factory()->create(['name' => 'SAVE20', 'discount' => 20]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
        ]);
        $order->products()->attach($product->id, ['color_id' => null, 'size_id' => null]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertJsonPath('data.order.products.0.name', 'Test Product');
        $response->assertJsonPath('data.order.coupon.name', 'SAVE20');
        $response->assertJsonPath('data.order.coupon.discount', 20);
    }

    /**
     * Authenticated pay returns checkout url and session_id from the PaymentGateway.
     * Mocks the gateway so CI never calls Stripe with live keys.
     */
    public function test_pay_returns_checkout_url_from_payment_gateway(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 29.99]);
        Sanctum::actingAs($user);

        $this->mock(PaymentGateway::class, function ($mock) use ($user) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->withArgs(function ($passedUser, $cartItems, $successUrl, $cancelUrl) use ($user) {
                    return $passedUser->is($user)
                        && $successUrl === 'https://example.test/success'
                        && $cancelUrl === 'https://example.test/cancel'
                        && is_array($cartItems)
                        && count($cartItems) === 1;
                })
                ->andReturn(new CheckoutResult(
                    url: 'https://checkout.stripe.test/cs_test_mock',
                    sessionId: 'cs_test_mock',
                    provider: 'stripe',
                ));
        });

        $response = $this->postJson(route('orders.pay'), [
            'cartItems' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                    'price' => 29.99,
                    'coupon_id' => null,
                ],
            ],
            'success_url' => 'https://example.test/success',
            'cancel_url' => 'https://example.test/cancel',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Checkout session created successfully');
        $response->assertJsonPath('data.url', 'https://checkout.stripe.test/cs_test_mock');
        $response->assertJsonPath('data.session_id', 'cs_test_mock');
    }

    /**
     * Pay requires authentication so anonymous clients cannot create checkout sessions.
     */
    public function test_pay_requires_authentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(route('orders.pay'), [
            'cartItems' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                    'price' => 10,
                ],
            ],
            'success_url' => 'https://example.test/success',
            'cancel_url' => 'https://example.test/cancel',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Pay validates required body fields (cartItems, success_url, cancel_url).
     */
    public function test_pay_returns_422_when_validation_fails(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('orders.pay'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cartItems', 'success_url', 'cancel_url']);
    }
}
