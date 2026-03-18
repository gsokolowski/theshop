<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\ReviewController. Covers store, update, destroy, check.
 */
class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Store creates a new review.
     */
    public function test_store_creates_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('reviews.store'), [
            'title' => 'Great product',
            'body' => 'Really enjoyed this item.',
            'rating' => 5,
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', 'Review created successfully, waiting for approval');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'title' => 'Great product',
            'approved' => false,
        ]);
    }

    /**
     * Store returns 400 when user already has a review for the product.
     */
    public function test_store_returns_400_when_user_already_has_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('reviews.store'), [
            'title' => 'Another review',
            'body' => 'Body text.',
            'rating' => 4,
            'product_id' => $product->id,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'User already has a review for this product');
    }

    /**
     * Store returns 422 when validation fails.
     */
    public function test_store_returns_422_when_validation_fails(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('reviews.store'), [
            'title' => '',
            'body' => '',
            'rating' => 10,
            'product_id' => 99999,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Update modifies review when user is owner.
     */
    public function test_update_modifies_review_when_owner(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('reviews.update', $review), [
            'title' => 'Updated title',
            'body' => 'Updated body.',
            'rating' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Review updated successfully');
        $review->refresh();
        $this->assertSame('Updated title', $review->title);
        $this->assertSame('Updated body.', $review->body);
        $this->assertSame(3.0, (float) $review->rating);
    }

    /**
     * Update returns 403 when user is not owner.
     */
    public function test_update_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('reviews.update', $review), [
            'title' => 'Hacked title',
            'body' => 'Hacked body.',
            'rating' => 1,
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'You are not the owner of this review');
    }

    /**
     * Destroy deletes review when user is owner.
     */
    public function test_destroy_deletes_review_when_owner(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('reviews.destroy', $review));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Review deleted successfully');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * Destroy returns 403 when user is not owner.
     */
    public function test_destroy_returns_403_when_not_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('reviews.destroy', $review));

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'You are not the owner of this review');
    }

    /**
     * Check returns has_review true when user has a review.
     */
    public function test_check_returns_true_when_user_has_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('reviews.check', ['product_id' => $product->id]));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Review check completed');
        $response->assertJsonPath('data.has_review', true);
    }

    /**
     * Check returns has_review false when user has no review.
     */
    public function test_check_returns_false_when_user_has_no_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('reviews.check', ['product_id' => $product->id]));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Review check completed');
        $response->assertJsonPath('data.has_review', false);
    }

    /**
     * Check returns 404 when product not found.
     */
    public function test_check_returns_404_when_product_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('reviews.check', ['product_id' => 99999]));

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'Product not found');
    }
}
