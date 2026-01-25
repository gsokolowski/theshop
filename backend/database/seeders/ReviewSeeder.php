<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Users or Products not found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        // Create reviews for products
        foreach ($products->take(10) as $product) {
            // 2-5 reviews per product
            $reviewCount = fake()->numberBetween(2, 5);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                Review::create([
                    'title' => fake()->sentence(rand(3, 6)),
                    'body' => fake()->paragraph(rand(2, 4)),
                    'rating' => fake()->numberBetween(3, 5),
                    'approved' => fake()->boolean(70), // 70% approved
                    'user_id' => $users->random()->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        // ✅ CHANGED: Create reviews manually to use existing users/products
        for ($i = 0; $i < 15; $i++) {
            Review::create([
                'title' => fake()->sentence(rand(3, 6)),
                'body' => fake()->paragraph(rand(2, 4)),
                'rating' => fake()->numberBetween(4, 5),
                'approved' => true,
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            Review::create([
                'title' => fake()->sentence(rand(3, 6)),
                'body' => fake()->paragraph(rand(2, 4)),
                'rating' => fake()->numberBetween(3, 5),
                'approved' => false,
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
            ]);
        }
    }
}