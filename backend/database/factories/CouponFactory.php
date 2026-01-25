<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->bothify('???###')), // e.g., ABC123
            'discount' => fake()->numberBetween(5, 50),
            'valid_until' => Carbon::now()->addDays(fake()->numberBetween(30, 365)),
        ];
    }

    // ✅ ADDED: State for valid coupons
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => Carbon::now()->addDays(fake()->numberBetween(30, 365)),
        ]);
    }

    // ✅ ADDED: State for expired coupons
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => Carbon::now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}