<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Users or Products not found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        // Select 30% of users
        $selectedUsersCount = max(1, (int) ceil($users->count() * 0.3));
        $selectedUsers = $users->random($selectedUsersCount);

        foreach ($selectedUsers as $user) {
            // Each user will have 1 to 3 items in their cart
            $cartItemsCount = \fake()->numberBetween(1, 3);

            for ($i = 0; $i < $cartItemsCount; $i++) {
                // Get a random product
                $product = $products->random();

                // Get colors and sizes that belong to this product
                $productColors = $product->colors;
                $productSizes = $product->sizes;

                // Skip if product has no colors or sizes
                if ($productColors->isEmpty() || $productSizes->isEmpty()) {
                    continue;
                }

                // Select random color and size from product's available options
                $color = $productColors->random();
                $size = $productSizes->random();

                // Check if this cart item already exists (unique constraint)
                $existingCart = Cart::where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->where('color_id', $color->id)
                    ->where('size_id', $size->id)
                    ->first();

                // Skip if cart item already exists
                if ($existingCart) {
                    continue;
                }

                // Create cart item
                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'quantity' => \fake()->numberBetween(1, 5),
                ]);
            }
        }

        $this->command->info('Cart items created for ' . $selectedUsersCount . ' users.');
    }
}
