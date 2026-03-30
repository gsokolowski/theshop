<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // API rate limits: route-specific limits, 60/min default for all other routes
        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            // Stricter for auth routes (10/min) to prevent brute-force
            if ($request->is('api/user/register') || $request->is('api/user/login')) {
                return Limit::perMinute(10)->by($key);
            }

            // More relaxed for product browsing (120/min)
            if ($request->is('api/products') || $request->is('api/products/*')) {
                return Limit::perMinute(100)->by($key);
            }

            // Default: 60 requests per minute for all other API routes
            return Limit::perMinute(100)->by($key);
        });

        // Force URL scheme for signed URLs to match the request
        // This ensures signature validation works correctly
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
