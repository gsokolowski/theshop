<?php

namespace App\Payments;

// This is DTO (Data Transfer Object) final class znd cannot be extended.
final class CheckoutResult
{
    public function __construct(
        // url — where the browser redirects (required)
        public readonly string $url,
        // sessionId — provider session id (optional; Stripe uses this today)
        public readonly ?string $sessionId = null,
        // provider — who created it (defaults to 'stripe'; useful when PayPal is added)
        public readonly string $provider = 'stripe',
    ) {}
}
