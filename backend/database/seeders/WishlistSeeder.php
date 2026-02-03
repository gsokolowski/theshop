<?php

namespace Database\Seeders;

use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
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
            // Each user will have 1 to 3 products in their wishlist
            $wishlistItemsCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $wishlistItemsCount; $i++) {
                // Get a random product
                $product = $products->random();

                // Check if this wishlist item already exists (unique constraint on user_id + product_id)
                $existingWishlist = Wishlist::where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->first();

                // Skip if wishlist item already exists
                if ($existingWishlist) {
                    continue;
                }

                // Create wishlist item
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        $this->command->info('Wishlist items created for ' . $selectedUsersCount . ' users.');
    }
}
