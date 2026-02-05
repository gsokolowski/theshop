<?php

namespace App\Providers;

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
        // Force URL scheme for signed URLs to match the request
        // This ensures signature validation works correctly
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
