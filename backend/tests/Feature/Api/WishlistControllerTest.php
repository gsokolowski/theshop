<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\WishlistController. Covers index, store, destroy.
 */
class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Index returns user's wishlist items.
     */
    public function test_index_returns_wishlist_items(): void
    {
        $user = User::factory()->create();
        Wishlist::factory()->count(2)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Wishlist items retrieved successfully');
        $this->assertCount(2, $response->json('data.wishlist_items'));
    }

    /**
     * Store creates new wishlist item.
     */
    public function test_store_creates_wishlist_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'Product added to wishlist successfully');
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    /**
     * Store returns 400 when product already in wishlist.
     */
    public function test_store_returns_400_when_already_in_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Wishlist::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Product is already in your wishlist');
    }

    /**
     * Store returns 422 when product not found (validation).
     */
    public function test_store_returns_422_when_product_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('wishlist.store'), [
            'product_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Destroy removes wishlist item.
     */
    public function test_destroy_removes_wishlist_item(): void
    {
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('wishlist.destroy', $wishlist));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Product removed from wishlist successfully');
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
    }

    /**
     * Destroy returns 403 when wishlist belongs to another user.
     */
    public function test_destroy_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('wishlist.destroy', $wishlist));

        $response->assertStatus(403);
    }

    /**
     * Index returns wishlist with out-of-stock product (WishlistResource status_badge).
     */
    public function test_index_includes_out_of_stock_product_status_badge(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => false]);
        Wishlist::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('data.wishlist_items.0.product.status_badge.label', 'Out of Stock');
        $response->assertJsonPath('data.wishlist_items.0.product.status_badge.class', 'warning');
    }
}
