<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(Request $request)
    {
        // Get all reviews with relationships (user, product)
        $query = Review::with('user', 'product')->latest();
        
        // Filter by approval status if filter parameter exists
        if ($request->has('filter')) {
            if ($request->filter === 'approved') {
                $query->where('approved', true);
            } elseif ($request->filter === 'unapproved') {
                $query->where('approved', false);
            }
        }
        
        $reviews = $query->get();
        
        // Display reviews view
        return view('admin.reviews.index', [
            'reviews' => $reviews
        ]);
    }

    /**
     * Update the approved status for a review (toggle).
     */
    public function update(Request $request, Review $review)
    {
        // Toggle approved status
        $review->update([
            'approved' => !$review->approved,
        ]);

        // Redirect back with success message
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review approval status updated successfully');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        // Delete the review
        $review->delete();

        // Redirect back with success message
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully');
    }
}
