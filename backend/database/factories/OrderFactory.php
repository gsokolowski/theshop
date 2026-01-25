<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 10, 200);
        $total = $qty * $price;

        return [
            'qty' => $qty,
            'total' => round($total, 2),
            'deliverd_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-30 days', 'now') : null, // 60% delivered
            'user_id' => User::factory(),
            'coupon_id' => null, // Will be set in seeder if needed
        ];
    }

    // ✅ ADDED: State for delivered orders
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'deliverd_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    // ✅ ADDED: State for pending orders
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'deliverd_at' => null,
        ]);
    }
}