<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Require an admin session even in local (parent allows all local by default).
        Horizon::auth(function ($request) {
            return Gate::check('viewHorizon', [$request->user()]);
        });
    }

    /**
     * Register the Horizon gate.
     *
     * Dashboard is limited to the Blade admin guard (not Sanctum customers).
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            $admin = auth('admin')->user();

            return $admin !== null && $admin->email === 'admin@email.com';
        });
    }
}
