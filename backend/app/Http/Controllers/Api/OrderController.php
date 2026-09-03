<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\PaymentCheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository,
        // ask PaymentService for a gateway; do not type-hint Stripe
        private PaymentService $paymentService,
    ) {}

    /**
     * Display a listing of the authenticated user's orders.
     * api: http://127.0.0.1:8000/api/orders
     */
    public function index(Request $request)
    {
        $orders = $this->orderRepository->listForUser($request->user());

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

    // Create a hosted checkout session (PaymentGateway; Phase 1 = Stripe)
    // api: http://127.0.0.1:8000/api/v1/orders/pay
    // ✅ CHANGED: payOrdersByStripe → pay; StripePaymentRequest → PaymentCheckoutRequest
    public function pay(PaymentCheckoutRequest $request)
    {
        try {
            $validated = $request->validated();

            $result = $this->paymentService->gateway()->createCheckout(
                $request->user(),
                $validated['cartItems'],
                $validated['success_url'],
                $validated['cancel_url'],
            );

            return response()->json([
                'message' => 'Checkout session created successfully',
                'data' => [
                    'url' => $result->url,
                    'session_id' => $result->sessionId,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create checkout session',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}