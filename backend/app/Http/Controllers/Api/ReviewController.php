<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewStoreRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    //create store method to store a new review
    // if user is not logged in, return error
    // if user is logged in, store the review
    // if user already has a review for the product, send message that user already has a review
    // use Review Store Request to validate the request
    // return the review
    // url: http://127.0.0.1:8000/api/reviews
    public function store(ReviewStoreRequest $request)
    {
        try {
            // Get validated data
            $validated = $request->validated();
            
            // Get authenticated user
            $user = $request->user();
            
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
            
            // Check if user already has a review for this product
            if ($user->reviews()->where('product_id', $product->id)->exists()) {
                return response()->json([
                    'message' => null,
                    'error' => 'User already has a review for this product',
                    'data' => null,
                    'status' => 400,
                ], 400);
            }
            
            // Set user_id from authenticated user
            $validated['user_id'] = $user->id;
            
            // Create review with validated data
            $review = Review::create([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'rating' => $validated['rating'],
                'product_id' => $validated['product_id'],
                'user_id' => $user->id, // Get user_id from authenticated user
                'approved' => false, // New reviews start as unapproved
            ]);
            
            // Load relationships
            $review->load('user', 'product');
            
            // Follow PREFERENCES.md API response format
            return response()->json([
                'message' => 'Review created successfully, waiting for approval',
                'data' => [
                    'review' => new ReviewResource($review),
                ],
                'status' => 201,
            ], 201);
            
        } catch (\Exception $e) {
            // Error handling following PREFERENCES.md format
            return response()->json([
                'message' => null,
                'error' => 'Failed to create review: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    // update review method to update a review
    // url: http://127.0.0.1:8000/api/reviews/{review}
    public function update(ReviewUpdateRequest $request, Review $review)
    {
        try {
            // Check if the user is the owner of the review
            if ($review->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => null,
                    'error' => 'You are not the owner of this review',
                    'data' => null,
                    'status' => 403,
                ], 403);
            }
            
            // Get validated data
            $validated = $request->validated();
            
            // Update the review
                $review->update([
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'rating' => $validated['rating'],
                    'approved' => false, // Updated reviews start as unapproved
                ]);
            
            // Refresh review to get latest data
            $review->refresh();
            
            // Load relationships
            $review->load('user', 'product');
            
            // Follow PREFERENCES.md API response format
            return response()->json([
                'message' => 'Review updated successfully',
                'data' => [
                    'review' => new ReviewResource($review),
                ],
                'status' => 200, // Integer instead of string 'success'
            ], 200);
            
        } catch (\Exception $e) {
            // Error handling following PREFERENCES.md format
            return response()->json([
                'message' => null,
                'error' => 'Failed to update review: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }

    // delete review method to delete a review
    // url: http://127.0.0.1:8000/api/reviews/{review}
    public function destroy(Request $request, Review $review)
    {
        try {
            // Check if the user is the owner of the review
            if ($review->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => null,
                    'error' => 'You are not the owner of this review',
                    'data' => null,
                    'status' => 403,
                ], 403);
            }
            
            // Delete the review
            $review->delete();
            
            // Follow PREFERENCES.md API response format
            return response()->json([
                'message' => 'Review deleted successfully',
                'error' => null,
                'data' => null,
                'status' => 200,
            ], 200);
            
        } catch (\Exception $e) {
            // Error handling following PREFERENCES.md format
            return response()->json([
                'message' => null,
                'error' => 'Failed to delete review: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ], 500);
        }
    }
}