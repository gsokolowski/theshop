<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthAdminRequest;
use App\Models\Admin;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // GET request to display admin dashboard
    public function index()
    {
        // Get todays orders use Carbon to get todays date
        $todayOrders = Order::whereDay('created_at', Carbon::today())->get();
        $yesterdayOrders = Order::whereDay('created_at', Carbon::yesterday())->get();
        $monthOrders = Order::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get();
        $yearOrders = Order::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->get();

        return view('admin.dashboard')->with([
            'todayOrders' => $todayOrders,
            'yesterdayOrders' => $yesterdayOrders,
            'monthOrders' => $monthOrders,
            'yearOrders' => $yearOrders,
        ]);
    }

    // GET request to display login form view if admin is not logged in
    public function loginForm()
    {
        // Check if admin is not logged in using auth guard
        // If not logged in, present loginForm view so user can login
        if (!auth()->guard('admin')->check()) {
            return view('loginForm');
        }
        // If logged in, redirect to admin dashboard
        return redirect()->route('admin.dashboard');
    }

    // POST request to login admin using AuthAdminRequest for validation
    public function auth(AuthAdminRequest $request)
    {
        // Validate request
        if($request->validated()) {

            // Attempt to login admin using auth guard admin
            $succesfullLogin = auth()->guard('admin')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]);
            
            // If login successful, regenerate session and redirect to admin dashboard
            if($succesfullLogin) {
                // Helps prevent session fixation attacks by invalidating the old session and creating a new one
                $request->session()->regenerate();

                // Redirect to admin dashboard with success message
                return redirect()->route('admin.dashboard')->with([
                    'success' => 'You are now logged in'
                ]);
            } else {
                // If login failed, redirect to route admin.loginForm  with error message
                return redirect()->route('admin.loginForm')->with([
                    'error' => 'These credentials do not match any of our records.'
                ]);
            }
        }      
    }

    // POST request to logout admin using auth guard admin
    public function logout()
    {
        // Logout the authenticated admin
        auth()->guard('admin')->logout();
        
        // Invalidate the session (remove all session data)
        request()->session()->invalidate();
        
        // Regenerate CSRF token (security measure)
        request()->session()->regenerateToken();
        
        // Redirect to route admin.loginForm with success message
        return redirect()->route('admin.loginForm')->with([
            'success' => 'You are now logged out'
        ]);
    }
}
