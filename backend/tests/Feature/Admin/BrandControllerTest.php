<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for BrandController. Covers all 7 resource actions (index, create, store,
 * show, edit, update, destroy) with admin authentication, validation rules, and slug-based
 * route model binding. Uses RefreshDatabase for isolation; each test runs against a fresh DB.
 */
class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate as an admin user. All brand routes are protected by the 'admin' middleware,
     * so we must log in with the admin guard before accessing them. Uses AdminFactory to create a
     * valid admin, then actingAs() to set the session for subsequent requests.
     */
    protected function asAdmin(): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Verifies that an authenticated admin can access the brands index page. We create 3 brands
     * to ensure the view receives data and displays them in latest-first order. Asserts the
     * correct view is rendered and the brands collection is passed with the expected count.
     */
    public function test_index_returns_200_with_brands_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Brand::factory()->count(3)->create();

        $response = $this->get(route('admin.brands.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.brands.index');
        $response->assertViewHas('brands');
        $this->assertCount(3, $response->viewData('brands'));
    }

    /**
     * Ensures unauthenticated users cannot access the brands list. The admin middleware should
     * redirect guests to the login page instead of exposing admin-only content.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.brands.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the brand creation form. The create action
     * returns a view without any existing brand data, so we only verify the view name and status.
     */
    public function test_create_returns_create_form_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.brands.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.brands.create');
    }

    /**
     * Ensures guests cannot access the create form. Without admin auth, the middleware redirects
     * to login to prevent unauthorized brand creation.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.brands.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for creating a brand. With valid input, the controller creates the
     * brand, auto-generates the slug from the name (via Str::slug), redirects to index, and
     * flashes a success message. We assert both the redirect/session and that the brand exists
     * in the database with the correct name and slug.
     */
    public function test_store_creates_brand_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.brands.store'), [
            'name' => 'Nike',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success', 'Brand created successfully');

        $this->assertDatabaseHas('brands', [
            'name' => 'Nike',
            'slug' => 'nike',
        ]);
    }

    /**
     * Ensures BrandStoreRequest validation rejects empty names. The 'required' rule should trigger,
     * session should contain validation errors, and no brand should be created in the database.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.brands.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('brands', 0);
    }

    /**
     * Ensures the unique:brands,name rule prevents duplicate brand names. We create one brand
     * first, then attempt to store another with the same name. Validation should fail and
     * the database should still contain only one brand.
     */
    public function test_store_fails_validation_when_name_already_exists(): void
    {
        $this->asAdmin();

        Brand::factory()->create(['name' => 'Adidas', 'slug' => 'adidas']);

        $response = $this->post(route('admin.brands.store'), [
            'name' => 'Adidas',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('brands', 1);
    }

    /**
     * Ensures guests cannot create brands via POST. The middleware blocks the request before
     * it reaches the controller, so no brand is created and the user is redirected to login.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.brands.store'), [
            'name' => 'Nike',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('brands', 0);
    }

    /**
     * The show action intentionally returns 404 for brands (no detail view exists). We verify
     * that any request to show a brand, even with a valid slug, results in a 404 response.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create();

        $response = $this->get(route('admin.brands.show', $brand->slug));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form for an existing brand.
     * We use the brand's slug in the URL (route model binding) and assert the correct view
     * is rendered with the brand passed to it.
     */
    public function test_edit_returns_edit_form_with_brand_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create(['name' => 'Puma', 'slug' => 'puma']);

        $response = $this->get(route('admin.brands.edit', $brand->slug));

        $response->assertStatus(200);
        $response->assertViewIs('admin.brands.edit');
        $response->assertViewHas('brand', $brand);
    }

    /**
     * Ensures guests cannot access the edit form. The middleware redirects unauthenticated
     * users to login before they can modify any brand.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->get(route('admin.brands.edit', $brand->slug));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Ensures invalid slugs are handled correctly. When route model binding cannot find a
     * brand for 'nonexistent-slug', Laravel returns a 404. This prevents exposing
     * internal errors or allowing enumeration attacks.
     */
    public function test_edit_returns_404_for_nonexistent_brand_slug(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.brands.edit', 'nonexistent-slug'));

        $response->assertStatus(404);
    }

    /**
     * Tests the happy path for updating a brand. With valid input, the controller updates
     * the brand, regenerates the slug from the new name, redirects to index, and flashes
     * success. We assert the redirect, session, and that the brand's name and slug were
     * updated in the database.
     */
    public function test_update_modifies_brand_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.brands.update', $brand->slug), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success', 'Brand updated successfully');

        $brand->refresh();
        $this->assertSame('New Name', $brand->name);
        $this->assertSame('new-name', $brand->slug);
    }

    /**
     * Ensures BrandUpdateRequest validation rejects empty names on update. The brand should
     * remain unchanged in the database and the session should contain validation errors.
     */
    public function test_update_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create(['name' => 'Puma', 'slug' => 'puma']);

        $response = $this->put(route('admin.brands.update', $brand->slug), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $brand->refresh();
        $this->assertSame('Puma', $brand->name);
    }

    /**
     * Ensures the unique rule on update prevents renaming a brand to match another existing
     * brand. We have Adidas and Nike; trying to rename Nike to Adidas should fail. The
     * unique rule excludes the current brand's id, so same-name updates are allowed elsewhere.
     */
    public function test_update_fails_validation_when_name_duplicates_another_brand(): void
    {
        $this->asAdmin();

        Brand::factory()->create(['name' => 'Adidas', 'slug' => 'adidas']);
        $brand = Brand::factory()->create(['name' => 'Nike', 'slug' => 'nike']);

        $response = $this->put(route('admin.brands.update', $brand->slug), [
            'name' => 'Adidas',
        ]);

        $response->assertSessionHasErrors(['name']);
        $brand->refresh();
        $this->assertSame('Nike', $brand->name);
    }

    /**
     * Ensures updating a brand without changing its name succeeds. The unique rule excludes
     * the current brand's id, so submitting the same name for the same brand is valid
     * (e.g. when the user only changes another field or submits the form unchanged).
     */
    public function test_update_allows_same_name_for_same_brand(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create(['name' => 'Nike', 'slug' => 'nike']);

        $response = $this->put(route('admin.brands.update', $brand->slug), [
            'name' => 'Nike',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success', 'Brand updated successfully');
        $brand->refresh();
        $this->assertSame('Nike', $brand->name);
    }

    /**
     * Ensures guests cannot update brands. The middleware blocks the PUT request, redirects
     * to login, and the brand remains unchanged in the database.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->put(route('admin.brands.update', $brand->slug), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.login'));
        $brand->refresh();
        $this->assertNotSame('New Name', $brand->name);
    }

    /**
     * Tests the happy path for deleting a brand. An authenticated admin can delete a brand,
     * and the controller redirects to index with a success flash message.
     */
    public function test_destroy_deletes_brand_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create();

        $response = $this->delete(route('admin.brands.destroy', $brand->slug));

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success', 'Brand deleted successfully');
    }

    /**
     * Ensures guests cannot delete brands. The middleware blocks the DELETE request and
     * redirects to login. The brand must still exist in the database afterward.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->delete(route('admin.brands.destroy', $brand->slug));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    /**
     * Verifies that a successful destroy request actually removes the brand from the
     * database. We perform the delete and then assert that Brand::find() returns null,
     * confirming the record was deleted.
     */
    public function test_destroy_removes_brand_from_database(): void
    {
        $this->asAdmin();

        $brand = Brand::factory()->create();

        $this->delete(route('admin.brands.destroy', $brand->slug));

        $this->assertNull(Brand::find($brand->id));
    }
}
