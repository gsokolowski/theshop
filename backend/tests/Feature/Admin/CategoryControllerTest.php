<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for CategoryController. Covers all 7 resource actions (index, create, store,
 * show, edit, update, destroy) with admin authentication, validation rules, and slug-based
 * route model binding. Uses RefreshDatabase for isolation.
 */
class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate as an admin user. All category routes are protected by the 'admin' middleware.
     */
    protected function asAdmin(): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Verifies that an authenticated admin can access the categories index page.
     */
    public function test_index_returns_200_with_categories_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Category::factory()->count(3)->create();

        $response = $this->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
        $response->assertViewHas('categories');
        $this->assertCount(3, $response->viewData('categories'));
    }

    /**
     * Ensures unauthenticated users cannot access the categories list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the category creation form.
     */
    public function test_create_returns_create_form_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.create');
    }

    /**
     * Ensures guests cannot access the create form.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.categories.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for creating a category.
     */
    public function test_store_creates_category_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Electronics',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category created successfully');

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);
    }

    /**
     * Ensures CategoryStoreRequest validation rejects empty names.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('categories', 0);
    }

    /**
     * Ensures the unique rule prevents duplicate category names.
     */
    public function test_store_fails_validation_when_name_already_exists(): void
    {
        $this->asAdmin();

        Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Electronics',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('categories', 1);
    }

    /**
     * Ensures guests cannot create categories.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Electronics',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('categories', 0);
    }

    /**
     * The show action intentionally returns 404 for categories.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create();

        $response = $this->get(route('admin.categories.show', $category->slug));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form for an existing category.
     */
    public function test_edit_returns_edit_form_with_category_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);

        $response = $this->get(route('admin.categories.edit', $category->slug));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.edit');
        $response->assertViewHas('category', $category);
    }

    /**
     * Ensures guests cannot access the edit form.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('admin.categories.edit', $category->slug));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Ensures invalid slugs return 404.
     */
    public function test_edit_returns_404_for_nonexistent_category_slug(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.categories.edit', 'nonexistent-slug'));

        $response->assertStatus(404);
    }

    /**
     * Tests the happy path for updating a category.
     */
    public function test_update_modifies_category_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.categories.update', $category->slug), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category updated successfully');

        $category->refresh();
        $this->assertSame('New Name', $category->name);
        $this->assertSame('new-name', $category->slug);
    }

    /**
     * Ensures validation rejects empty names on update.
     */
    public function test_update_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);

        $response = $this->put(route('admin.categories.update', $category->slug), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $category->refresh();
        $this->assertSame('Electronics', $category->name);
    }

    /**
     * Ensures the unique rule prevents renaming to match another category.
     */
    public function test_update_fails_validation_when_name_duplicates_another_category(): void
    {
        $this->asAdmin();

        Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        $category = Category::factory()->create(['name' => 'Clothing', 'slug' => 'clothing']);

        $response = $this->put(route('admin.categories.update', $category->slug), [
            'name' => 'Electronics',
        ]);

        $response->assertSessionHasErrors(['name']);
        $category->refresh();
        $this->assertSame('Clothing', $category->name);
    }

    /**
     * Ensures updating without changing name succeeds.
     */
    public function test_update_allows_same_name_for_same_category(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);

        $response = $this->put(route('admin.categories.update', $category->slug), [
            'name' => 'Electronics',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category updated successfully');
        $category->refresh();
        $this->assertSame('Electronics', $category->name);
    }

    /**
     * Ensures guests cannot update categories.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $category = Category::factory()->create();

        $response = $this->put(route('admin.categories.update', $category->slug), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.login'));
        $category->refresh();
        $this->assertNotSame('New Name', $category->name);
    }

    /**
     * Tests the happy path for deleting a category.
     */
    public function test_destroy_deletes_category_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create();

        $response = $this->delete(route('admin.categories.destroy', $category->slug));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category deleted successfully');
    }

    /**
     * Ensures guests cannot delete categories.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.categories.destroy', $category->slug));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Verifies that destroy actually removes the category from the database.
     */
    public function test_destroy_removes_category_from_database(): void
    {
        $this->asAdmin();

        $category = Category::factory()->create();

        $this->delete(route('admin.categories.destroy', $category->slug));

        $this->assertNull(Category::find($category->id));
    }
}
