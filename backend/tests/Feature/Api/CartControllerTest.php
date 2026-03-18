<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\CartController. Covers index, store, update, destroy.
 */
class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Index returns user's cart items.
     */
    public function test_index_returns_cart_items(): void
    {
        $user = User::factory()->create();
        Cart::factory()->count(2)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('cart.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Cart items retrieved successfully');
        $this->assertCount(2, $response->json('data.cart_items'));
    }

    /**
     * Store creates new cart item.
     */
    public function test_store_creates_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 10, 'status' => true]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'Item added to cart successfully');
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    /**
     * Store increases quantity when item already exists.
     */
    public function test_store_increases_quantity_when_item_exists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 10, 'status' => true]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 2,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Cart item quantity updated successfully');
        $this->assertDatabaseHas('carts', ['quantity' => 5]);
    }

    /**
     * Store returns 400 when product is out of stock.
     */
    public function test_store_returns_400_when_product_out_of_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 10, 'status' => false]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Product is out of stock');
    }

    /**
     * Store returns 400 when quantity exceeds available stock.
     */
    public function test_store_returns_400_when_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 3, 'status' => true]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Quantity exceeds available stock. Maximum available: 3');
    }

    /**
     * Store returns 400 when quantity exceeds stock when increasing existing item.
     */
    public function test_store_returns_400_when_quantity_exceeds_stock_on_existing_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 5, 'status' => true]);
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 3,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Quantity exceeds available stock. Maximum available: 5');
    }

    /**
     * Store returns 422 when product not found (validation).
     */
    public function test_store_returns_422_when_product_not_found(): void
    {
        $user = User::factory()->create();
        $color = Color::factory()->create();
        $size = Size::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('cart.store'), [
            'product_id' => 99999,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Update modifies cart item quantity.
     */
    public function test_update_modifies_cart_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 10, 'status' => true]);
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('cart.update', $cart), [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Cart item quantity updated successfully');
        $cart->refresh();
        $this->assertSame(5, $cart->quantity);
    }

    /**
     * Update returns 403 when cart belongs to another user.
     */
    public function test_update_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('cart.update', $cart), [
            'quantity' => 5,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Update returns 400 when product is out of stock.
     */
    public function test_update_returns_400_when_product_out_of_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 10, 'status' => false]);
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('cart.update', $cart), [
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Product is out of stock');
    }

    /**
     * Update returns 400 when quantity exceeds available stock.
     */
    public function test_update_returns_400_when_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['qty' => 3, 'status' => true]);
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('cart.update', $cart), [
            'quantity' => 5,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Quantity exceeds available stock. Maximum available: 3');
    }

    /**
     * Index returns empty array when user has no cart items.
     */
    public function test_index_returns_empty_when_no_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('cart.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Cart items retrieved successfully');
        $this->assertCount(0, $response->json('data.cart_items'));
    }

    /**
     * Index requires authentication.
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson(route('cart.index'));

        $response->assertStatus(401);
    }

    /**
     * Destroy removes cart item.
     */
    public function test_destroy_removes_cart_item(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('cart.destroy', $cart));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Cart item removed successfully');
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    /**
     * Destroy returns 403 when cart belongs to another user.
     */
    public function test_destroy_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('cart.destroy', $cart));

        $response->assertStatus(403);
    }
}
