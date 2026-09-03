<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class PaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {}

    /**
     * Phase 1: always the bound gateway (Stripe).
     * Phase 2: gateway('stripe'|'paypal').
     */
    public function gateway(): PaymentGateway
    {
        return $this->gateway;
    }
}
