<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $coupons = Coupon::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Users or Products not found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        // Create orders with relationships
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $product = $products->random();
            $qty = fake()->numberBetween(1, 5);
            $price = $product->price;
            
            // 30% chance of using a coupon
            $coupon = fake()->boolean(30) ? $coupons->random() : null;

            // Calculate total with coupon discount
            $total = $qty * $price;
            if ($coupon && $coupon->isValid()) {
                $discount = $total * ($coupon->discount / 100);
                $total = $total - $discount;
            }

            $order = Order::create([
                'qty' => $qty,
                'total' => round($total, 2),
                'user_id' => $user->id,
                'coupon_id' => $coupon ? $coupon->id : null,
                'deliverd_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-30 days', 'now') : null,
            ]);

            // Attach product(s) to order
            $order->products()->attach($product->id);
            
            // 20% chance of multiple products in one order
            if (fake()->boolean(20) && $products->count() > 1) {
                $additionalProduct = $products->where('id', '!=', $product->id)->random();
                $order->products()->attach($additionalProduct->id);
            }
        }
    }
}