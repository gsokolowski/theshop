<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        // Get users based on filter parameter
        $query = User::query();
        
        if ($request->has('filter')) {
            if ($request->filter === 'deleted') {
                // Get only soft-deleted users
                $query->onlyTrashed();
            } else {
                // Get only active users (default behavior)
                $query->withoutTrashed();
            }
        } else {
            // Default: show active users only
            $query->withoutTrashed();
        }
        
        $users = $query->latest()->get();
        
        // Display users view
        return view('admin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Remove the specified user from storage (soft delete).
     */
    public function destroy(User $user)
    {
        // Soft delete the user (sets deleted_at timestamp)
        // Profile image, orders, and reviews remain in database
        $user->delete();

        // Redirect back with success message and preserve filter
        $filter = request()->get('filter');
        if ($filter === 'deleted') {
            return redirect()->route('admin.users.index', ['filter' => 'deleted'])
                ->with('success', 'User deleted successfully');
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

        /**
     * Restore the specified soft-deleted user.
     */
    public function restore($id)
    {
        // Find the soft-deleted user
        $user = User::onlyTrashed()->findOrFail($id);
        
        // Restore the user (removes deleted_at timestamp)
        $user->restore();

        // Redirect back with success message
        return redirect()->route('admin.users.index', ['filter' => 'deleted'])
            ->with('success', 'User restored successfully');
    }
}
