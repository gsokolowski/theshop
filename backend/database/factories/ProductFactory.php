<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(rand(2, 4), true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'qty' => fake()->numberBetween(0, 100),
            'price' => fake()->randomFloat(2, 9.99, 299.99),
            'description' => fake()->paragraph(rand(2, 4)),
            'thumbnail' => null,
            'first_image' => null,
            'second_image' => null,
            'third_image' => null,
            'status' => fake()->boolean(80) ? 1 : 0,
            'category_id' => Category::factory(), // ✅ Now works!
            'brand_id' => Brand::factory(), // ✅ Now works!
        ];
    }

    // ✅ ADDED: State for in-stock products
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'qty' => fake()->numberBetween(10, 100),
        ]);
    }

    // ✅ ADDED: State for out-of-stock products
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
            'qty' => 0,
        ]);
    }
}