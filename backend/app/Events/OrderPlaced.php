<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<int, int>  $orderIds
     */
    public function __construct(
        public User $user,
        public array $orderIds,
    ) {}
}
