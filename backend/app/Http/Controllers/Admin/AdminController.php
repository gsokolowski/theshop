<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthAdminRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // GET request to display login form view if admin is not logged in
    public function login()
    {
        // Authentication check(): Returns true if admin is authenticated, false otherwise
        if (auth()->guard('admin')->check()) { 
            return redirect()->route('admin.dashboard');
        }
        // If not admin is notauthenticated , redirect to login view so user can login
        return view('login'); // login.blade.php
    }

    // POST request to login admin using AuthAdminRequest for validation
    public function auth(AuthAdminRequest $request)
    {
        // Validate request
        if($request->validated()) { // if request is valid, proceed to login admin

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
                // If login failed, redirect to route admin.login with error message so user can try again
                return redirect()->route('admin.login')->with([
                    'error' => 'These credentials do not match any of our records.'
                ]);
            }
        }      
    }

    // POST request to logout admin using auth guard admin
    public function logout(Request $request)
    {
        // Logout admin if authenticated (safe to call even if not logged in)
        auth()->guard('admin')->logout();
        
        // Always invalidate session and regenerate CSRF token for security
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect to route admin.login with success message so user can login again
        return redirect()->route('admin.login')->with([
            'success' => 'You are now logged out'
        ]);
    }

    // GET request to display admin dashboard
    public function dashboard()
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
}
