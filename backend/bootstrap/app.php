<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;    

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable throttle middleware on API routes (uses 'api' limiter from AppServiceProvider)
        $middleware->throttleApi();

        // add/register Admin middleware to admin routes
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
        
        // Trust all proxies for signed URL validation
        // This ensures signature validation works correctly when behind a proxy
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API clients (e.g. Postman without Accept: application/json) must get 401 JSON, not a
        // redirect to route('login') — this app has no web "login" route (only admin.login).
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
