<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for UserController. Covers index (with filter), destroy (soft delete), and restore.
 */
class UserControllerTest extends TestCase
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
     * Verifies that an authenticated admin can access the users index page.
     */
    public function test_index_returns_200_with_users_list_for_authenticated_admin(): void
    {
        $this->asAdmin();

        User::factory()->count(3)->create();

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
        $this->assertCount(3, $response->viewData('users'));
    }

    /**
     * Index can filter to show only soft-deleted users.
     */
    public function test_index_filters_deleted_users_when_filter_param_provided(): void
    {
        $this->asAdmin();

        User::factory()->count(2)->create();
        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $response = $this->get(route('admin.users.index', ['filter' => 'deleted']));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('users'));
    }

    /**
     * Ensures unauthenticated users cannot access the users list.
     */
    public function test_index_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Tests soft-deleting a user.
     */
    public function test_destroy_soft_deletes_user_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User deleted successfully');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Ensures guests cannot delete users.
     */
    public function test_destroy_redirects_to_login_when_unauthenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Tests restoring a soft-deleted user.
     */
    public function test_restore_restores_user_and_redirects_with_success(): void
    {
        $this->asAdmin();

        $user = User::factory()->create();
        $user->delete();

        $response = $this->post(route('admin.users.restore', $user->id));

        $response->assertRedirect(route('admin.users.index', ['filter' => 'deleted']));
        $response->assertSessionHas('success', 'User restored successfully');

        $user->refresh();
        $this->assertNull($user->deleted_at);
    }

    /**
     * Ensures guests cannot restore users.
     */
    public function test_restore_redirects_to_login_when_unauthenticated(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->post(route('admin.users.restore', $user->id));

        $response->assertRedirect(route('admin.login'));
    }
}
