<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class OrderPolicy
{
    /**
     * Determine if the user can view any orders (index).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Determine if the user can create orders.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the order (e.g. set delivery status).
     * Only admins can update orders.
     */
    public function update(Authenticatable $user, Order $order): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine if the user can delete the order.
     * Only admins can delete orders.
     */
    public function delete(Authenticatable $user, Order $order): bool
    {
        return $user instanceof Admin;
    }
}
