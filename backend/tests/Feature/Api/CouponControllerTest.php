<?php

namespace Tests\Feature\Api;

use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for Api\CouponController. Covers getCouponByName (valid and invalid).
 */
class CouponControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * getCouponByName returns coupon when valid.
     */
    public function test_get_coupon_by_name_returns_valid_coupon(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $coupon = Coupon::factory()->create([
            'name' => 'SAVE20',
            'valid_until' => Carbon::now()->addDays(30),
        ]);

        $response = $this->getJson(route('coupon.get', ['name' => 'SAVE20']));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Coupon applied successfully');
        $response->assertJsonPath('data.name', 'SAVE20');
    }

    /**
     * getCouponByName returns 404 when coupon does not exist.
     */
    public function test_get_coupon_by_name_returns_404_when_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('coupon.get', ['name' => 'INVALID']));

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'Invalid or expired coupon');
    }

    /**
     * getCouponByName returns 404 when coupon is expired.
     */
    public function test_get_coupon_by_name_returns_404_when_expired(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Coupon::factory()->expired()->create(['name' => 'EXPIRED']);

        $response = $this->getJson(route('coupon.get', ['name' => 'EXPIRED']));

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'Invalid or expired coupon');
    }
}
