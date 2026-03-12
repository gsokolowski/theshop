<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\StripePaymentRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use App\Models\Cart;

class OrderController extends Controller
{
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
            DB::beginTransaction();
            
            $validated = $request->validated();
            $createdOrders = [];
            $user = $request->user();
            
            foreach ($validated['cartItems'] as $item) {
                $order = Order::create([
                    'qty' => $item['qty'],
                    'user_id' => $user->id,
                    'coupon_id' => $item['coupon_id'] ?? null,
                    'total' => $this->calculateEachOrderTotal(
                        $item['qty'], 
                        $item['price'], 
                        $item['coupon_id'] ?? null
                    ),
                ]);
                // ✅ CHANGED: Attach product with color_id and size_id in pivot table
                $order->products()->attach($item['product_id'], [
                    'color_id' => $item['color_id'],
                    'size_id' => $item['size_id'],
                ]);
                $createdOrders[] = $order->load('products', 'user', 'coupon');
            }
            
            // Delete all cart items for the user after orders are created
            Cart::where('user_id', $user->id)->delete();
            
            DB::commit();
    
            return response()->json([
                'message' => 'Orders stored successfully',
                'data' => [
                    'user' => UserResource::make($user->fresh()),
                    'orders' => $createdOrders
                ]
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create orders',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // create calculateEachOrderTotal method to calculate the total for each order with discount if coupon is valid
    public function calculateEachOrderTotal($qty, $price, $coupon_id)
    {
        $discount = 0;
        $total = $price * $qty;
        
        // Check if coupon_id exists before querying
        if ($coupon_id) {
            $coupon = Coupon::find($coupon_id);
            
            if ($coupon && $coupon->isValid()) {
                $discount = $total * ($coupon->discount / 100);
            }
        }
        
        $orderTotal = $total - $discount;
        return $orderTotal;
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
                            'name' => 'The Store'
                        ],
                        'unit_amount' => $this->calculateTotalToPay($validated['cartItems'])
                    ],
                    'quantity' => 1
                ]],
                'mode' => 'payment',
                // the url to redirect to after the payment is successful
                'success_url' => $successUrlWithSession,
                // the url to redirect to after the payment is cancelled
                'cancel_url' => $validated['cancel_url'],
                'metadata' => [
                    'user_id' => $request->user()->id,
                    'cart_items' => json_encode($validated['cartItems'])
                ]
            ]);
            //return the link to the stripe checkout form
            return response()->json([
                'message' => 'Checkout session created successfully',
                'data' => [
                    'url' => $checkout_session->url,
                    'session_id' => $checkout_session->id
                ]
            ], 200);

        } catch (ApiErrorException $e) {
            // ✅ ADDED: Better error handling
            return response()->json([
                'message' => 'Failed to create checkout session',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) { // ✅ ADDED: Catch general exceptions
            return response()->json([
                'message' => 'Failed to create checkout session',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Calculate the total to pay
     */
    public function calculateTotalToPay($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $this->calculateEachOrderTotal(
                $item['qty'],
                $item['price'],
                $item['coupon_id'] ?? null
            );
        }
        return (int) ($total * 100);
    }
}
