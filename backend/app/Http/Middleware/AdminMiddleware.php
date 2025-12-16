<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if user is admin using auth guard
        // if admin, allow access to route
        
        if (auth()->guard('admin')->check()) {
            return $next($request);
        }
        // if not admin, redirect to admin login page
        return redirect()->route('admin.login')->with('error', 'You are not authorized to access this page');
    }
}
