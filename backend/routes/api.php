<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\Auth\GoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// All API routes are versioned under /api/v1/
Route::prefix('v1')->group(function () {
    // User routes are protected and require authentication using Laravel Sanctum API token.
    Route::middleware('auth:sanctum')->group(function () {
        // url: http://127.0.0.1:8000/api/v1/user
    Route::get('/user', [UserController::class, 'loggedInUser'])->name('user.loggedInUser');
    // url: http://127.0.0.1:8000/api/v1/user/logout
    Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');
    // url: http://127.0.0.1:8000/api/v1/user/profile/update
    Route::put('/user/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');
    // url: http://127.0.0.1:8000/api/v1/user/password/update
    Route::put('/user/password/update', [UserController::class, 'updatePassword'])->name('user.password.update');
    // url: http://127.0.0.1:8000/api/v1/user
    Route::delete('/user', [UserController::class, 'destroy'])->name('user.destroy');
    // url: http://127.0.0.1:8000/api/v1/coupon/{name} and pass the coupon name as route  parameter
    Route::get('/coupon/{name}', [CouponController::class, 'getCouponByName'])->name('coupon.get');
    // url: http://127.0.0.1:8000/api/v1/orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // url: http://127.0.0.1:8000/api/v1/orders
    Route::post('/orders', [OrderController::class, 'storeUserCartItemsOrders'])->name('orders.store');
    // url: http://127.0.0.1:8000/api/v1/orders/pay
    Route::post('/orders/pay', [OrderController::class, 'payOrdersByStripe'])->name('orders.pay');
    // url: http://127.0.0.1:8000/api/v1/orders/{order}
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    // url: http://127.0.0.1:8000/api/v1/reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    // url: http://127.0.0.1:8000/api/v1/reviews/{review}
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    // url: http://127.0.0.1:8000/api/v1/reviews/{review}
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    // url: http://127.0.0.1:8000/api/v1/reviews/check/{product}
    Route::get('/reviews/check/{product_id}', [ReviewController::class, 'check'])->name('reviews.check');
    // url: http://127.0.0.1:8000/api/v1/cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // url: http://127.0.0.1:8000/api/v1/cart
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    // url: http://127.0.0.1:8000/api/v1/cart/{cart}
    Route::put('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    // url: http://127.0.0.1:8000/api/v1/cart/{cart}
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    // url: http://127.0.0.1:8000/api/v1/wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    // url: http://127.0.0.1:8000/api/v1/wishlist
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    // url: http://127.0.0.1:8000/api/v1/wishlist/{wishlist}
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    // url: http://127.0.0.1:8000/api/v1/email/verification/resend - Resend verification email
    Route::post('/email/verification/resend', [UserController::class, 'resendVerificationEmail'])->name('email.verification.resend');
    });

    // url: http://127.0.0.1:8000/api/v1/user/register
    Route::post('/user/register', [UserController::class, 'register'])->name('user.register');
    // url: http://127.0.0.1:8000/api/v1/user/login
    Route::post('/user/login', [UserController::class, 'login'])->name('user.login');
    // url: http://127.0.0.1:8000/api/v1/email/verify - Email verification (public route, uses signed URL)
    Route::get('/email/verify', [UserController::class, 'verifyEmail'])->name('email.verify');

    // Google OAuth routes (no auth middleware needed, but session middleware required for Socialite)
    // url: http://127.0.0.1:8000/api/v1/auth/google
    Route::middleware('web')->group(function () {
        Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
        // url: http://127.0.0.1:8000/api/v1/auth/google/callback
        Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
    });

    // Product routes use Api\ProductController.php to handle the requests and are opened to all users.
    // url: http://127.0.0.1:8000/api/v1/products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // url: http://127.0.0.1:8000/api/v1/products/search/{searchTerm}
    Route::get('/products/search/{searchTerm}', [ProductController::class, 'filterBySearchTerm'])->name('products.filter.searchTerm');
    // url: http://127.0.0.1:8000/api/v1/products/{product}
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    // url: http://127.0.0.1:8000/api/v1/products/category/{category}
    Route::get('/products/category/{category}', [ProductController::class, 'filterByCategory'])->name('products.filter.category');
    // url: http://127.0.0.1:8000/api/v1/products/brand/{brand} - brand is the slug of the brand
    Route::get('/products/brand/{brand}', [ProductController::class, 'filterByBrand'])->name('products.filter.brand');
    // url: http://127.0.0.1:8000/api/v1/products/color/{color} - color is the slug of the color
    Route::get('/products/color/{color}', [ProductController::class, 'filterByColor'])->name('products.filter.color');
    // url: http://127.0.0.1:8000/api/v1/products/size/{size} - size is the slug of the size
    Route::get('/products/size/{size}', [ProductController::class, 'filterBySize'])->name('products.filter.size');
});

