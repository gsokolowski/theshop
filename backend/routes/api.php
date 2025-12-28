<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Product routes  use Api\ProductController.php to handle the requests
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// show a single product
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
// filter products by category
Route::get('/products/category/{category}', [ProductController::class, 'filterByCategory'])->name('products.filter.category');
// filter products by brand
Route::get('/products/brand/{brand}', [ProductController::class, 'filterByBrand'])->name('products.filter.brand');
// filter products by color
Route::get('/products/color/{color}', [ProductController::class, 'filterByColor'])->name('products.filter.color');
// filter products by size
Route::get('/products/size/{size}', [ProductController::class, 'filterBySize'])->name('products.filter.size');
// filter products by search term
Route::get('/products/search/{search}', [ProductController::class, 'filterBySearchTerm'])->name('products.filter.search');


