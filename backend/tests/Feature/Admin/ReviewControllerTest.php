<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for ReviewController. Covers index (with filter), update (toggle approval), and destroy.
 */
class ReviewControllerTest extends TestCase
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
     * Verifies that an authenticated admin can access the reviews index page.
     */
    public function test_index_returns_200_with_reviews_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Review::factory()->count(3)->create();

        $response = $this->get(route('admin.reviews.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reviews.index');
        $response->assertViewHas('reviews');
        $this->assertCount(3, $response->viewData('reviews'));
    }

    /**
     * Index can filter by approved status.
     */
    public function test_index_filters_by_approved_when_filter_param_provided(): void
    {
        $this->asAdmin();

        Review::factory()->approved()->count(2)->create();
        Review::factory()->pending()->count(3)->create();

        $response = $this->get(route('admin.reviews.index', ['filter' => 'approved']));

        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('reviews'));
    }

    /**
     * Index can filter by unapproved status.
     */
    public function test_index_filters_by_unapproved_when_filter_param_provided(): void
    {
        $this->asAdmin();

        Review::factory()->approved()->count(2)->create();
        Review::factory()->pending()->count(3)->create();

        $response = $this->get(route('admin.reviews.index', ['filter' => 'unapproved']));

        $response->assertStatus(200);
        $this->assertCount(3, $response->viewData('reviews'));
    }

    /**
     * Ensures unauthenticated users cannot access the reviews list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.reviews.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests toggling review approval status.
     */
    public function test_update_toggles_approval_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $review = Review::factory()->pending()->create();

        $response = $this->put(route('admin.reviews.update', $review));

        $response->assertRedirect(route('admin.reviews.index'));
        $response->assertSessionHas('success', 'Review approval status updated successfully');

        $review->refresh();
        $this->assertTrue((bool) $review->approved);
    }

    /**
     * Ensures guests cannot update reviews.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $review = Review::factory()->create();

        $response = $this->put(route('admin.reviews.update', $review));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for deleting a review.
     */
    public function test_destroy_deletes_review_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $review = Review::factory()->create();

        $response = $this->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect(route('admin.reviews.index'));
        $response->assertSessionHas('success', 'Review deleted successfully');
    }

    /**
     * Verifies that destroy actually removes the review from the database.
     */
    public function test_destroy_removes_review_from_database(): void
    {
        $this->asAdmin();

        $review = Review::factory()->create();

        $this->delete(route('admin.reviews.destroy', $review));

        $this->assertNull(Review::find($review->id));
    }

    /**
     * Ensures guests cannot delete reviews.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
