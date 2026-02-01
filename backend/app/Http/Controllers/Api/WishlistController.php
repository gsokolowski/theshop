<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WishlistStoreRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the authenticated user's wishlist items.
     * url: http://127.0.0.1:8000/api/wishlist
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
            
            // Get all wishlist items for authenticated user with relationships
            $wishlistItems = Wishlist::with('product.category', 'product.brand', 'product.colors', 'product.sizes')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
            
            return response()->json([
                'message' => 'Wishlist items retrieved successfully',
                'data' => [
                    'wishlist_items' => WishlistResource::collection($wishlistItems),
                ],
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to retrieve wishlist items: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Store a newly created wishlist item.
     * url: http://127.0.0.1:8000/api/wishlist
     */
    public function store(WishlistStoreRequest $request)
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
            
            // Check if product exists
            $product = Product::find($validated['product_id']);
            if (!$product) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product not found',
                    'data' => null,
                    'status' => 404,
                ], 404);
            }
            
            // Check if wishlist item already exists for this user + product
            $existingWishlistItem = Wishlist::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->first();
            
            if ($existingWishlistItem) {
                return response()->json([
                    'message' => null,
                    'error' => 'Product is already in your wishlist',
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Create new wishlist item
            $wishlistItem = Wishlist::create([
                'user_id' => $user->id, // Get from authenticated user
                'product_id' => $validated['product_id'],
            ]);
            
            // Load relationships
            $wishlistItem->load('product.category', 'product.brand', 'product.colors', 'product.sizes');
            
            return response()->json([
                'message' => 'Product added to wishlist successfully',
                'data' => [
                    'wishlist_item' => new WishlistResource($wishlistItem),
                ],
                'status' => 201,
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to add item to wishlist: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Remove the specified wishlist item.
     * url: http://127.0.0.1:8000/api/wishlist/{wishlist}
     */
    public function destroy(Request $request, Wishlist $wishlist)
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
            
            // Check if wishlist item belongs to authenticated user
            if ($wishlist->user_id !== $user->id) {
                return response()->json([
                    'message' => null,
                    'error' => 'You are not authorized to delete this wishlist item',
                    'data' => null,
                    'status' => 403,
                ], 403);
            }
            
            // Delete wishlist item
            $wishlist->delete();
            
            return response()->json([
                'message' => 'Product removed from wishlist successfully',
                'error' => null,
                'data' => null,
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => null,
                'error' => 'Failed to remove wishlist item: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }
}
