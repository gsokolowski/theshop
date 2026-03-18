<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for AdminController. Covers login, auth, logout, and dashboard.
 * Login/logout routes are outside admin middleware; dashboard requires admin auth.
 */
class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to authenticate as an admin user for routes protected by admin middleware.
     */
    protected function asAdmin(): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Unauthenticated users see the login form. The login route is public.
     */
    public function test_login_returns_login_view_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertViewIs('login');
    }

    /**
     * Authenticated admins are redirected to dashboard when visiting login.
     */
    public function test_login_redirects_to_dashboard_when_authenticated(): void
    {
        $this->asAdmin();

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Valid credentials log the admin in and redirect to dashboard with success message.
     */
    public function test_auth_logs_in_admin_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123', // Admin model casts to hashed
        ]);

        $response = $this->post(route('admin.auth'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success', 'You are now logged in');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /**
     * Invalid credentials redirect to login with error message.
     */
    public function test_auth_fails_with_invalid_credentials(): void
    {
        Admin::factory()->create(['email' => 'admin@test.com']);

        $response = $this->post(route('admin.auth'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error', 'These credentials do not match any of our records.');
    }

    /**
     * Logout invalidates session and redirects to login with success message.
     */
    public function test_logout_redirects_to_login_and_invalidates_session(): void
    {
        $this->asAdmin();

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('success', 'You are now logged out');
    }

    /**
     * Authenticated admin can access dashboard with order stats.
     */
    public function test_dashboard_returns_200_with_order_stats_for_authenticated_admin(): void
    {
        $this->asAdmin();

        Order::factory()->count(2)->create();

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas(['todayOrders', 'yesterdayOrders', 'monthOrders', 'yearOrders']);
    }

    /**
     * Unauthenticated users cannot access dashboard and are redirected to login.
     */
    public function test_dashboard_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }
}
