<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Color;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for ColorController. Covers all 7 resource actions with admin authentication.
 * Color uses id for route model binding (no slug).
 */
class ColorControllerTest extends TestCase
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
     * Verifies that an authenticated admin can access the colors index page.
     */
    public function test_index_returns_200_with_colors_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Color::factory()->count(3)->create();

        $response = $this->get(route('admin.colors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.colors.index');
        $response->assertViewHas('colors');
        $this->assertCount(3, $response->viewData('colors'));
    }

    /**
     * Ensures unauthenticated users cannot access the colors list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.colors.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Confirms that an authenticated admin can access the color creation form.
     */
    public function test_create_returns_create_form_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.colors.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.colors.create');
    }

    /**
     * Ensures guests cannot access the create form.
     */
    public function test_create_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.colors.create'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for creating a color.
     */
    public function test_store_creates_color_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.colors.store'), [
            'name' => 'Navy',
        ]);

        $response->assertRedirect(route('admin.colors.index'));
        $response->assertSessionHas('success', 'Color created successfully');

        $this->assertDatabaseHas('colors', ['name' => 'Navy']);
    }

    /**
     * Ensures ColorStoreRequest validation rejects empty names.
     */
    public function test_store_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.colors.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('colors', 0);
    }

    /**
     * Ensures the unique rule prevents duplicate color names.
     */
    public function test_store_fails_validation_when_name_already_exists(): void
    {
        $this->asAdmin();

        Color::factory()->create(['name' => 'Navy']);

        $response = $this->post(route('admin.colors.store'), [
            'name' => 'Navy',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('colors', 1);
    }

    /**
     * Ensures guests cannot create colors.
     */
    public function test_store_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->post(route('admin.colors.store'), [
            'name' => 'Navy',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('colors', 0);
    }

    /**
     * The show action intentionally returns 404 for colors.
     */
    public function test_show_returns_404(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create();

        $response = $this->get(route('admin.colors.show', $color));

        $response->assertStatus(404);
    }

    /**
     * Verifies that an authenticated admin can access the edit form.
     */
    public function test_edit_returns_edit_form_with_color_for_authenticated_admin(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create(['name' => 'Navy']);

        $response = $this->get(route('admin.colors.edit', $color));

        $response->assertStatus(200);
        $response->assertViewIs('admin.colors.edit');
        $response->assertViewHas('color', $color);
    }

    /**
     * Ensures guests cannot access the edit form.
     */
    public function test_edit_redirects_to_login_when_unauthenticated(): void
    {
        $color = Color::factory()->create();

        $response = $this->get(route('admin.colors.edit', $color));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests the happy path for updating a color.
     */
    public function test_update_modifies_color_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create(['name' => 'Old Name']);

        $response = $this->put(route('admin.colors.update', $color), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.colors.index'));
        $response->assertSessionHas('success', 'Color updated successfully');

        $color->refresh();
        $this->assertSame('New Name', $color->name);
    }

    /**
     * Ensures validation rejects empty names on update.
     */
    public function test_update_fails_validation_when_name_is_empty(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create(['name' => 'Navy']);

        $response = $this->put(route('admin.colors.update', $color), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $color->refresh();
        $this->assertSame('Navy', $color->name);
    }

    /**
     * Ensures the unique rule prevents renaming to match another color.
     */
    public function test_update_fails_validation_when_name_duplicates_another_color(): void
    {
        $this->asAdmin();

        Color::factory()->create(['name' => 'Navy']);
        $color = Color::factory()->create(['name' => 'Red']);

        $response = $this->put(route('admin.colors.update', $color), [
            'name' => 'Navy',
        ]);

        $response->assertSessionHasErrors(['name']);
        $color->refresh();
        $this->assertSame('Red', $color->name);
    }

    /**
     * Ensures updating without changing name succeeds.
     */
    public function test_update_allows_same_name_for_same_color(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create(['name' => 'Navy']);

        $response = $this->put(route('admin.colors.update', $color), [
            'name' => 'Navy',
        ]);

        $response->assertRedirect(route('admin.colors.index'));
        $response->assertSessionHas('success', 'Color updated successfully');
        $color->refresh();
        $this->assertSame('Navy', $color->name);
    }

    /**
     * Ensures guests cannot update colors.
     */
    public function test_update_redirects_to_login_when_unauthenticated(): void
    {
        $color = Color::factory()->create();

        $response = $this->put(route('admin.colors.update', $color), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.login'));
        $color->refresh();
        $this->assertNotSame('New Name', $color->name);
    }

    /**
     * Tests the happy path for deleting a color.
     */
    public function test_destroy_deletes_color_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create();

        $response = $this->delete(route('admin.colors.destroy', $color));

        $response->assertRedirect(route('admin.colors.index'));
        $response->assertSessionHas('success', 'Color deleted successfully');
    }

    /**
     * Ensures guests cannot delete colors.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $color = Color::factory()->create();

        $response = $this->delete(route('admin.colors.destroy', $color));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('colors', ['id' => $color->id]);
    }

    /**
     * Verifies that destroy actually removes the color from the database.
     */
    public function test_destroy_removes_color_from_database(): void
    {
        $this->asAdmin();

        $color = Color::factory()->create();

        $this->delete(route('admin.colors.destroy', $color));

        $this->assertNull(Color::find($color->id));
    }
}
