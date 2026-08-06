<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createFromCartItems(User $user, array $cartItems): array
    {
        $createdOrders = DB::transaction(function () use ($user, $cartItems) {
            $createdOrders = [];

            foreach ($cartItems as $item) {
                $order = Order::create([
                    'qty' => $item['qty'],
                    'user_id' => $user->id, // never from the client
                    'coupon_id' => $item['coupon_id'] ?? null,
                    'total' => $this->calculateEachOrderTotal(
                        $item['qty'],
                        $item['price'],
                        $item['coupon_id'] ?? null
                    ),
                ]);

                $order->products()->attach($item['product_id'], [
                    'color_id' => $item['color_id'],
                    'size_id' => $item['size_id'],
                ]);

                $createdOrders[] = $order->load('products', 'user', 'coupon');
            }

            Cart::where('user_id', $user->id)->delete();

            return $createdOrders;
        });

        // Dispatch after commit so a Redis/worker race cannot load missing orders
        SendOrderConfirmationEmail::dispatch(
            $user,
            collect($createdOrders)->pluck('id')->toArray()
        );

        return $createdOrders;
    }

    public function calculateEachOrderTotal(int $qty, float $price, ?int $couponId): float
    {
        $total = $price * $qty;
        $discount = 0;

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon && $coupon->isValid()) {
                $discount = $total * ($coupon->discount / 100);
            }
        }

        return $total - $discount;
    }

    public function calculateTotalToPayInCents(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $this->calculateEachOrderTotal(
                $item['qty'],
                $item['price'],
                $item['coupon_id'] ?? null
            );
        }

        return (int) ($total * 100);
    }
}