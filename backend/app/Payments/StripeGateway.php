<?php

namespace App\Payments;

use App\Contracts\PaymentGateway;
use App\Models\User;
use App\Services\OrderService;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class StripeGateway implements PaymentGateway
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * @param  array<int, array{product_id: int, qty: int, price: float|int, coupon_id?: int|null}>  $cartItems
     */
    public function createCheckout(
        User $user,
        array $cartItems,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutResult {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Append Stripe's session placeholder; keep ? vs & if the URL already has query params
        $separator = str_contains($successUrl, '?') ? '&' : '?';
        $successUrlWithSession = $successUrl.$separator.'session_id={CHECKOUT_SESSION_ID}';

        $checkoutSession = StripeSession::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'The Store',
                    ],
                    'unit_amount' => $this->orderService->calculateTotalToPayInCents($cartItems),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrlWithSession,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => (string) $user->id,
                'cart_items' => json_encode($cartItems, JSON_THROW_ON_ERROR),
            ],
        ]);

        return new CheckoutResult(
            url: $checkoutSession->url,
            sessionId: $checkoutSession->id,
            provider: 'stripe',
        );
    }
}
