<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// User routes are protected and require authentication using Laravel Sanctum API token.
Route::middleware('auth:sanctum')->group(function () {
    // url: http://127.0.0.1:8000/api/user
    Route::get('/user', [UserController::class, 'loggedInUser'])->name('user.loggedInUser');
    // url: http://127.0.0.1:8000/api/user/logout
    Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');
    // url: http://127.0.0.1:8000/api/user/profile/update
    Route::put('/user/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');
    // url: http://127.0.0.1:8000/api/user/password/update
    Route::put('/user/password/update', [UserController::class, 'updatePassword'])->name('user.password.update');
    // url: http://127.0.0.1:8000/api/user
    Route::delete('/user', [UserController::class, 'destroy'])->name('user.destroy');
    // url: http://127.0.0.1:8000/api/coupon/{name} and pass the coupon name as route  parameter
    Route::get('/coupon/{name}', [CouponController::class, 'getCouponByName'])->name('coupon.get');
    // url: http://127.0.0.1:8000/api/orders
    Route::post('/orders', [OrderController::class, 'storeUserCartItemsOrders'])->name('orders.store');
});

// url: http://127.0.0.1:8000/api/user/register
Route::post('/user/register', [UserController::class, 'register'])->name('user.register');
// url: http://127.0.0.1:8000/api/user/login
Route::post('/user/login', [UserController::class, 'login'])->name('user.login');


// Product routes  use Api\ProductController.php to handle the requests and are opened to all users.
// url: http://127.0.0.1:8000/api/products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// url: http://127.0.0.1:8000/api/products/search?searchTerm=your-search-term
Route::get('/products/search', [ProductController::class, 'filterBySearchTerm'])->name('products.filter.searchTerm');
// url: http://127.0.0.1:8000/api/products/{product}
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
// url: http://127.0.0.1:8000/api/products/category/{category}
Route::get('/products/category/{category}', [ProductController::class, 'filterByCategory'])->name('products.filter.category');
// url: http://127.0.0.1:8000/api/products/brand/{brand} - brand is the slug of the brand
Route::get('/products/brand/{brand}', [ProductController::class, 'filterByBrand'])->name('products.filter.brand');
// url: http://127.0.0.1:8000/api/products/color/{color} - color is the slug of the color
Route::get('/products/color/{color}', [ProductController::class, 'filterByColor'])->name('products.filter.color');
// url: http://127.0.0.1:8000/api/products/size/{size} - size is the slug of the size
Route::get('/products/size/{size}', [ProductController::class, 'filterBySize'])->name('products.filter.size');

