<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderRepository
{
    /**
     * Orders for a user, newest first, with relations for the API.
     */
    public function listForUser(User $user): Collection
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->with(['products.colors', 'products.sizes', 'coupon'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Persist a new order row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Order
    {
        return Order::create($attributes);
    }
}
