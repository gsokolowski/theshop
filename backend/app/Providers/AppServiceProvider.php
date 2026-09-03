<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Payments\StripeGateway;
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
        // ✅ ADDED: Phase 1 always uses Stripe; PaymentService resolves PaymentGateway from this binding
        $this->app->bind(PaymentGateway::class, StripeGateway::class);
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

        // Force HTTPS for signed URLs in deployed environments. Skip testing so APP_URL (e.g. http://localhost)
        // matches the URL Laravel signs and the HTTP test client uses.
        if (! in_array(config('app.env'), ['local', 'testing'], true)) {
            URL::forceScheme('https');
        }
    }
}
