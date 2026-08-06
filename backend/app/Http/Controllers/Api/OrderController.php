<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\StripePaymentRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Display a listing of the authenticated user's orders.
     * api: http://127.0.0.1:8000/api/orders
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['products.colors', 'products.sizes', 'coupon'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => OrderResource::collection($orders),
            ],
        ], 200);
    }

    /**
     * Display the specified order.
     * api: http://127.0.0.1:8000/api/orders/{order}
     */
    public function show(Request $request, Order $order)
    {
        // Calls Laravel’s authorization.
        // Uses OrderPolicy::view() under the hood.
        // Policy logic: $order->user_id === $user->id (only the order owner can view it).
        $this->authorize('view', $order);

        // with and load are both eager loading relatioshihip
        // with is used on the query  load on existing model instance e.g route model binding
        // You already have $order (from route model binding) this is why use load instead of with
        $order->load(['products.colors', 'products.sizes', 'coupon']);

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => [
                'order' => OrderResource::make($order),
            ],
        ], 200);
    }

    // create store method to store the order
    // api: http://127.0.0.1:8000/api/orders
    public function storeUserCartItemsOrders(OrderStoreRequest $request)
    {
        try {
            $user = $request->user();
            $createdOrders = $this->orderService->createFromCartItems(
                $user,
                $request->validated()['cartItems']
            );

            return response()->json([
                'message' => 'Orders stored successfully',
                'data' => [
                    'user' => UserResource::make($user->fresh()),
                    'orders' => $createdOrders,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create orders',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // pay for the orders with stripe payment gateway
    // api: http://127.0.0.1:8000/api/orders/pay
    public function payOrdersByStripe(StripePaymentRequest $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $validated = $request->validated();
            // check if URL already has route params
            $successUrl = $validated['success_url'];
            $separator = str_contains($successUrl, '?') ? '&' : '?';
            $successUrlWithSession = $successUrl . $separator . 'session_id={CHECKOUT_SESSION_ID}';

            $checkout_session = StripeSession::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'The Store',
                        ],
                        'unit_amount' => $this->orderService->calculateTotalToPayInCents(
                            $validated['cartItems']
                        ),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                // the url to redirect to after the payment is successful
                'success_url' => $successUrlWithSession,
                // the url to redirect to after the payment is cancelled
                'cancel_url' => $validated['cancel_url'],
                'metadata' => [
                    'user_id' => $request->user()->id,
                    'cart_items' => json_encode($validated['cartItems']),
                ],
            ]);

            // return the link to the stripe checkout form
            return response()->json([
                'message' => 'Checkout session created successfully',
                'data' => [
                    'url' => $checkout_session->url,
                    'session_id' => $checkout_session->id,
                ],
            ], 200);
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Failed to create checkout session',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create checkout session',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}