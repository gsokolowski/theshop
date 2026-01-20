<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // create store method to store the order
    // api: http://127.0.0.1:8000/api/orders
    public function storeUserCartItemsOrders(OrderStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $createdOrders = [];

            foreach ($validated['cartItems'] as $item) {
                $order = Order::create([
                    'qty' => $item['qty'],
                    'user_id' => $request->user()->id,
                    'coupon_id' => $item['coupon_id'] ?? null, // ✅ CHANGED: Use null coalescing
                    'total' => $this->calculateEachOrderTotal(
                        $item['qty'], 
                        $item['price'], 
                        $item['coupon_id'] ?? null // ✅ CHANGED: Use null coalescing
                    ),
                ]);
                // attach the product to the order_product pivot table using the product_id
                $order->products()->attach($item['product_id']); 
                // load the products, user and coupon for the order using the load method to avoid n+1 queries
                $createdOrders[] = $order;
            }

            DB::commit();

            return response()->json([
                'message' => 'Orders stored successfully',
                'data' => $createdOrders, // ✅ CHANGED: Include data field
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create orders',
                'data' => null,
            ], 500);
        }
    }
    
    // create calculateEachOrderTotal method to calculate the total for each order with discount if coupon is valid
    public function calculateEachOrderTotal($qty, $price, $coupon_id)
    {
        $discount = 0;
        $total = $price * $qty;
        
        // ✅ CHANGED: Check if coupon_id exists before querying
        if ($coupon_id) {
            $coupon = Coupon::find($coupon_id);
            
            if ($coupon && $coupon->isValid()) {
                $discount = $total * ($coupon->discount / 100);
            }
        }
        
        $orderTotal = $total - $discount;
        return $orderTotal;
    }
}
