<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderUpdateRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders.
     */
    public function index()
    {
        // Get all orders with relationships (user, products, coupon)
        $orders = Order::with('user', 'products', 'coupon')
            ->latest()
            ->get();
        
        // Display orders view
        return view('admin.orders.index', [
            'orders' => $orders
        ]);
    }

        /**
     * Update the delivery_at field for an order.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        // auth()->guard('admin')->user() returns the logged-in Admin model.
        // authorizeForUser($user, 'update', $order) checks whether that admin can update the order.
        // Internally calls OrderPolicy::update() with the Admin and Order. The policy returns true if the user is an Admin.
        // If denied, Laravel throws an AuthorizationException and returns a 403 response.
        $this->authorizeForUser(auth()->guard('admin')->user(), 'update', $order);

        // Validation already happened automatically via FormRequest
        // If we reach here, validation passed
        
        // Update deliverd_at field
        // If deliverd_at is provided, set it; otherwise set to null
        $order->update([
            'deliverd_at' => $request->deliverd_at ? $request->deliverd_at : null,
        ]);

        // Redirect back with success message
        return redirect()->route('admin.orders.index')
            ->with('success', 'Order delivery status updated successfully');
    }

        /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $this->authorizeForUser(auth()->guard('admin')->user(), 'delete', $order);

        // Delete the order (relationships will be handled by database cascade or manually)
        // Note: You may want to detach products first if not using cascade delete
        $order->products()->detach();
        $order->delete();

        // Redirect back with success message
        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully');
    }
}
