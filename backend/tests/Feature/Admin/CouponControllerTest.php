<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for CouponController. Covers all 7 resource actions with admin authentication.
 * Coupon uses id for route model binding. Has name, discount, valid_until fields.
 */
class CouponControllerTest extends TestCase
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
     * Verifies that an authenticated admin can access the coupons index page.
     */
    public function test_index_returns_200_with_coupons_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Coupon::factory()->count(3)->create();

        $response = $this->get(route('admin.coupons.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.coupons.index');
        $response->assertViewHas('coupons');
        $this->assertCount(3, $response->viewData('coupons'));
    }

    /**
     * Ensures unauthenticated users cannot access the coupons list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.coupons.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the coupon creation form.
     */
    public function test_create_returns_create_form_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.coupons.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.coupons.create');
    }

    /**
     * Ensures guests cannot access the create form.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.coupons.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for creating a coupon.
     */
    public function test_store_creates_coupon_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $validUntil = Carbon::now()->addDays(30)->format('Y-m-d H:i');

        $response = $this->post(route('admin.coupons.store'), [
            'name' => 'SAVE20',
            'discount' => 20,
            'valid_until' => $validUntil,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success', 'Coupon created successfully');

        $this->assertDatabaseHas('coupons', [
            'name' => 'SAVE20',
            'discount' => 20,
        ]);
    }

    /**
     * Ensures CouponStoreRequest validation rejects empty name.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.coupons.store'), [
            'name' => '',
            'discount' => 20,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('coupons', 0);
    }

    /**
     * Ensures the unique rule prevents duplicate coupon names.
     */
    public function test_store_fails_validation_when_name_already_exists(): void
    {
        $this->asAdmin();

        Coupon::factory()->create(['name' => 'SAVE20']);

        $response = $this->post(route('admin.coupons.store'), [
            'name' => 'SAVE20',
            'discount' => 15,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('coupons', 1);
    }

    /**
     * Ensures guests cannot create coupons.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.coupons.store'), [
            'name' => 'SAVE20',
            'discount' => 20,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('coupons', 0);
    }

    /**
     * The show action intentionally returns 404 for coupons.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create();

        $response = $this->get(route('admin.coupons.show', $coupon));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form.
     */
    public function test_edit_returns_edit_form_with_coupon_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create();

        $response = $this->get(route('admin.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertViewIs('admin.coupons.edit');
        $response->assertViewHas('coupon', $coupon);
    }

    /**
     * Ensures guests cannot access the edit form.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->get(route('admin.coupons.edit', $coupon));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for updating a coupon.
     */
    public function test_update_modifies_coupon_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create(['name' => 'SAVE10', 'discount' => 10]);
        $validUntil = Carbon::now()->addDays(60)->format('Y-m-d H:i');

        $response = $this->put(route('admin.coupons.update', $coupon), [
            'name' => 'SAVE25',
            'discount' => 25,
            'valid_until' => $validUntil,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success', 'Coupon updated successfully');

        $coupon->refresh();
        $this->assertSame('SAVE25', $coupon->name);
        $this->assertSame(25, $coupon->discount);
    }

    /**
     * Ensures validation rejects empty name on update.
     */
    public function test_update_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create();

        $response = $this->put(route('admin.coupons.update', $coupon), [
            'name' => '',
            'discount' => 20,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Ensures the unique rule prevents renaming to match another coupon.
     */
    public function test_update_fails_validation_when_name_duplicates_another_coupon(): void
    {
        $this->asAdmin();

        Coupon::factory()->create(['name' => 'SAVE20']);
        $coupon = Coupon::factory()->create(['name' => 'SAVE10']);

        $response = $this->put(route('admin.coupons.update', $coupon), [
            'name' => 'SAVE20',
            'discount' => 15,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['name']);
        $coupon->refresh();
        $this->assertSame('SAVE10', $coupon->name);
    }

    /**
     * Ensures guests cannot update coupons.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->put(route('admin.coupons.update', $coupon), [
            'name' => 'SAVE30',
            'discount' => 30,
            'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for deleting a coupon.
     */
    public function test_destroy_deletes_coupon_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create();

        $response = $this->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success', 'Coupon deleted successfully');
    }

    /**
     * Ensures guests cannot delete coupons.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
    }

    /**
     * Verifies that destroy actually removes the coupon from the database.
     */
    public function test_destroy_removes_coupon_from_database(): void
    {
        $this->asAdmin();

        $coupon = Coupon::factory()->create();

        $this->delete(route('admin.coupons.destroy', $coupon));

        $this->assertNull(Coupon::find($coupon->id));
    }
}
