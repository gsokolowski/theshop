<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SizeController;
use Illuminate\Support\Facades\Route;

// General route to acction login 
Route::get('/',[AdminController::class,"login"])->name("admin.login");
Route::post('/admin/auth',[AdminController::class,"auth"])->name("admin.auth");

// Admin routes with middleware admin  Url, Controller, Name of the route
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // Category indidivual routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Brand individual routes
    Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('admin.brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('admin.brands.show');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

    // Color individual routes
    Route::get('/colors', [ColorController::class, 'index'])->name('admin.colors.index');
    Route::get('/colors/create', [ColorController::class, 'create'])->name('admin.colors.create');
    Route::post('/colors', [ColorController::class, 'store'])->name('admin.colors.store');
    Route::get('/colors/{color}', [ColorController::class, 'show'])->name('admin.colors.show');
    Route::get('/colors/{color}/edit', [ColorController::class, 'edit'])->name('admin.colors.edit');
    Route::put('/colors/{color}', [ColorController::class, 'update'])->name('admin.colors.update');
    Route::delete('/colors/{color}', [ColorController::class, 'destroy'])->name('admin.colors.destroy');

    // Size individual routes
    Route::get('/sizes', [SizeController::class, 'index'])->name('admin.sizes.index');
    Route::get('/sizes/create', [SizeController::class, 'create'])->name('admin.sizes.create');
    Route::post('/sizes', [SizeController::class, 'store'])->name('admin.sizes.store');
    Route::get('/sizes/{size}', [SizeController::class, 'show'])->name('admin.sizes.show');
    Route::get('/sizes/{size}/edit', [SizeController::class, 'edit'])->name('admin.sizes.edit');
    Route::put('/sizes/{size}', [SizeController::class, 'update'])->name('admin.sizes.update');
    Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('admin.sizes.destroy');

    // Product individual routes
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    // Coupon individual routes
    Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('/coupons/{coupon}', [CouponController::class, 'show'])->name('admin.coupons.show');
    Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');

    // Order individual routes
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

    // Review individual routes
    Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('admin.reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
});
