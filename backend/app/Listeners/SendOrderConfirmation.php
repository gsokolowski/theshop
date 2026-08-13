<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOrderConfirmationEmail;

class SendOrderConfirmation
{
    /**
     * Queue the confirmation email after an order is placed.
     */
    public function handle(OrderPlaced $event): void
    {
        SendOrderConfirmationEmail::dispatch($event->user, $event->orderIds);
    }
}
