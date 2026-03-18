<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for SizeController. Covers all 7 resource actions with admin authentication.
 * Size uses id for route model binding (no slug).
 */
class SizeControllerTest extends TestCase
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
     * Verifies that an authenticated admin can access the sizes index page.
     */
    public function test_index_returns_200_with_sizes_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Size::factory()->count(3)->create();

        $response = $this->get(route('admin.sizes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sizes.index');
        $response->assertViewHas('sizes');
        $this->assertCount(3, $response->viewData('sizes'));
    }

    /**
     * Ensures unauthenticated users cannot access the sizes list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.sizes.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the size creation form.
     */
    public function test_create_returns_create_form_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.sizes.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sizes.create');
    }

    /**
     * Ensures guests cannot access the create form.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.sizes.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for creating a size.
     */
    public function test_store_creates_size_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.sizes.store'), [
            'name' => 'M',
        ]);

        $response->assertRedirect(route('admin.sizes.index'));
        $response->assertSessionHas('success', 'Size created successfully');

        $this->assertDatabaseHas('sizes', ['name' => 'M']);
    }

    /**
     * Ensures SizeStoreRequest validation rejects empty names.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.sizes.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('sizes', 0);
    }

    /**
     * Ensures the unique rule prevents duplicate size names.
     */
    public function test_store_fails_validation_when_name_already_exists(): void
    {
        $this->asAdmin();

        Size::factory()->create(['name' => 'M']);

        $response = $this->post(route('admin.sizes.store'), [
            'name' => 'M',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('sizes', 1);
    }

    /**
     * Ensures guests cannot create sizes.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.sizes.store'), [
            'name' => 'M',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('sizes', 0);
    }

    /**
     * The show action intentionally returns 404 for sizes.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create();

        $response = $this->get(route('admin.sizes.show', $size));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form.
     */
    public function test_edit_returns_edit_form_with_size_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create(['name' => 'M']);

        $response = $this->get(route('admin.sizes.edit', $size));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sizes.edit');
        $response->assertViewHas('size', $size);
    }

    /**
     * Ensures guests cannot access the edit form.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $size = Size::factory()->create();

        $response = $this->get(route('admin.sizes.edit', $size));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for updating a size.
     */
    public function test_update_modifies_size_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create(['name' => 'S']);

        $response = $this->put(route('admin.sizes.update', $size), [
            'name' => 'M',
        ]);

        $response->assertRedirect(route('admin.sizes.index'));
        $response->assertSessionHas('success', 'Size updated successfully');

        $size->refresh();
        $this->assertSame('M', $size->name);
    }

    /**
     * Ensures validation rejects empty names on update.
     */
    public function test_update_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create(['name' => 'M']);

        $response = $this->put(route('admin.sizes.update', $size), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $size->refresh();
        $this->assertSame('M', $size->name);
    }

    /**
     * Ensures the unique rule prevents renaming to match another size.
     */
    public function test_update_fails_validation_when_name_duplicates_another_size(): void
    {
        $this->asAdmin();

        Size::factory()->create(['name' => 'M']);
        $size = Size::factory()->create(['name' => 'L']);

        $response = $this->put(route('admin.sizes.update', $size), [
            'name' => 'M',
        ]);

        $response->assertSessionHasErrors(['name']);
        $size->refresh();
        $this->assertSame('L', $size->name);
    }

    /**
     * Ensures updating without changing name succeeds.
     */
    public function test_update_allows_same_name_for_same_size(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create(['name' => 'M']);

        $response = $this->put(route('admin.sizes.update', $size), [
            'name' => 'M',
        ]);

        $response->assertRedirect(route('admin.sizes.index'));
        $response->assertSessionHas('success', 'Size updated successfully');
        $size->refresh();
        $this->assertSame('M', $size->name);
    }

    /**
     * Ensures guests cannot update sizes.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $size = Size::factory()->create(['name' => 'M']);

        $response = $this->put(route('admin.sizes.update', $size), [
            'name' => 'XL',
        ]);

        $response->assertRedirect(route('admin.login'));
        $size->refresh();
        $this->assertNotSame('XL', $size->name);
    }

    /**
     * Tests the happy path for deleting a size.
     */
    public function test_destroy_deletes_size_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create();

        $response = $this->delete(route('admin.sizes.destroy', $size));

        $response->assertRedirect(route('admin.sizes.index'));
        $response->assertSessionHas('success', 'Size deleted successfully');
    }

    /**
     * Ensures guests cannot delete sizes.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $size = Size::factory()->create();

        $response = $this->delete(route('admin.sizes.destroy', $size));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('sizes', ['id' => $size->id]);
    }

    /**
     * Verifies that destroy actually removes the size from the database.
     */
    public function test_destroy_removes_size_from_database(): void
    {
        $this->asAdmin();

        $size = Size::factory()->create();

        $this->delete(route('admin.sizes.destroy', $size));

        $this->assertNull(Size::find($size->id));
    }
}
