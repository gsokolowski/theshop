<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display a listing of the authenticated user's cart items.
     * url: http://127.0.0.1:8000/api/cart
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Ensure user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => null,
                    'error' => 'User not authenticated',
                    'data' => null,
                    'status' => 401,
                ], 401);
            }
            
            // Get all cart items for authenticated user with relationships
            $cartItems = Cart::with('product', 'color', 'size')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
            
            return response()->json([
                'message' => 'Cart items retrieved successfully',
                'data' => [
                    'cart_items' => CartResource::collection($cartItems),
                ],
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to retrieve cart items: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Store a newly created cart item.
     * url: http://127.0.0.1:8000/api/cart
     */
    public function store(CartStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();
            
            // Ensure user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => null,
                    'error' => 'User not authenticated',
                    'data' => null,
                    'status' => 401,
                ], 401);
            }
            
            // Check if product exists and is in stock
            $product = Product::find($validated['product_id']);
            if (!$product) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product not found',
                    'data' => null,
                    'status' => 404,
                ], 404);
            }
            
            if (!$product->status) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product is out of stock',
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Check if cart item already exists (same user, product, color, size)
            $existingCartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->where('color_id', $validated['color_id'])
                ->where('size_id', $validated['size_id'])
                ->first();
            
            if ($existingCartItem) {
                // Increase quantity if item exists
                $newQuantity = $existingCartItem->quantity + $validated['quantity'];
                
                // Check if new quantity exceeds product stock
                if ($newQuantity > $product->qty) {
                    return response()->json([
                        'message' => null,
                        'error' => 'Quantity exceeds available stock. Maximum available: ' . $product->qty,
                        'data' => null,
                        'status' => 400,
                    ], 400);
                }
                
                $existingCartItem->update(['quantity' => $newQuantity]);
                $existingCartItem->load('product', 'color', 'size');
                
                return response()->json([
                    'message' => 'Cart item quantity updated successfully',
                    'data' => [
                        'cart_item' => new CartResource($existingCartItem),
                    ],
                    'status' => 200,
                ], 200);
            }
            
            // Check if quantity exceeds product stock
            if ($validated['quantity'] > $product->qty) {
                return response()->json([
                    'message' => null,
                    'error' => 'Quantity exceeds available stock. Maximum available: ' . $product->qty,
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Create new cart item
            Log::info('Creating cart item', [
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
                'color_id' => $validated['color_id'],
                'size_id' => $validated['size_id'],
                'quantity' => $validated['quantity'],
            ]);
            
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
                'color_id' => $validated['color_id'],
                'size_id' => $validated['size_id'],
                'quantity' => $validated['quantity'],
            ]);
            
            Log::info('Cart item created successfully', ['cart_id' => $cartItem->id]);
            
            $cartItem->load('product', 'color', 'size');
            
            return response()->json([
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_item' => new CartResource($cartItem),
                ],
                'status' => 201,
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Failed to add item to cart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => null,
                'error' => 'Failed to add item to cart: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Update the specified cart item quantity.
     * url: http://127.0.0.1:8000/api/cart/{cart}
     */
    public function update(CartUpdateRequest $request, Cart $cart)
    {
        try {
            $user = $request->user();
            
            // Ensure user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => null,
                    'error' => 'User not authenticated',
                    'data' => null,
                    'status' => 401,
                ], 401);
            }
            
            // Check if cart item belongs to authenticated user
            if ($cart->user_id !== $user->id) {
                return response()->json([
                    'message' => null,
                    'error' => 'You are not authorized to update this cart item',
                    'data' => null,
                    'status' => 403,
                ], 403);
            }
            
            $validated = $request->validated();
            
            // Check if product exists and is in stock
            $product = $cart->product;
            if (!$product) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product not found',
                    'data' => null,
                    'status' => 404,
                ], 404);
            }
            
            if (!$product->status) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product is out of stock',
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Check if quantity exceeds product stock
            if ($validated['quantity'] > $product->qty) {
                return response()->json([
                    'message' => null,
                    'error' => 'Quantity exceeds available stock. Maximum available: ' . $product->qty,
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Update cart item quantity
            $cart->update([
                'quantity' => $validated['quantity'],
            ]);
            
            $cart->load('product', 'color', 'size');
            
            return response()->json([
                'message' => 'Cart item quantity updated successfully',
                'data' => [
                    'cart_item' => new CartResource($cart),
                ],
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to update cart item: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Remove the specified cart item.
     * url: http://127.0.0.1:8000/api/cart/{cart}
     */
    public function destroy(Request $request, Cart $cart)
    {
        try {
            $user = $request->user();
            
            // Ensure user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => null,
                    'error' => 'User not authenticated',
                    'data' => null,
                    'status' => 401,
                ], 401);
            }
            
            // Check if cart item belongs to authenticated user
            if ($cart->user_id !== $user->id) {
                return response()->json([
                    'message' => null,
                    'error' => 'You are not authorized to delete this cart item',
                    'data' => null,
                    'status' => 403,
                ], 403);
            }
            
            // Delete cart item
            $cart->delete();
            
            return response()->json([
                'message' => 'Cart item removed successfully',
                'error' => null,
                'data' => null,
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to remove cart item: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }
}
