<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
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


});
