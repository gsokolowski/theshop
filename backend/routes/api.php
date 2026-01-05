<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Product routes  use Api\ProductController.php to handle the requests
// url: http://127.0.0.1:8000/api/products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
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
// url: http://127.0.0.1:8000/api/products/search/{search} - search is the search term
Route::get('/products/search/{searchTerm}', [ProductController::class, 'filterBySearchTerm'])->name('products.filter.searchTerm');


