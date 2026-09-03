<?php

namespace App\Contracts;

use App\Models\User;
use App\Payments\CheckoutResult;

interface PaymentGateway
{
    /**
     * Create a hosted checkout session and return the redirect URL.
     * Start a hosted checkout for this user and cart, then give me something I can send the browser to
     * “Hosted checkout”  is provider’s page (e.g. Stripe Checkout)
     *
     * @param  array<int, array{product_id: int, qty: int, price: float|int, coupon_id?: int|null}>  $cartItems
     */
    public function createCheckout(
        // Who is paying (for metadata / ownership — not taken from the client as a raw user_id)
        User $user,
        // Line items used to compute the amount and stash cart context
        array $cartItems,
        // Where to send the user after a successful payment
        string $successUrl,
        //Where to send them if they cancel
        string $cancelUrl,
    ): CheckoutResult;
}
