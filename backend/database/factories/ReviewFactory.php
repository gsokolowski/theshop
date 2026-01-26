<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(rand(3, 6)),
            'body' => fake()->paragraph(rand(2, 4)),
            'rating' => fake()->randomFloat(1, 3.5, 5.0), // 3-5 stars
            'approved' => fake()->boolean(70), // 70% approved
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
        ];
    }

    // State for approved reviews
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => true,
            'rating' => fake()->randomFloat(1, 4.5, 5.0), // Approved reviews tend to be higher rated
        ]);
    }

    // State for pending reviews
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => false,
        ]);
    }
}