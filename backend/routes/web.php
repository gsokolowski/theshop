<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// General route to login form
Route::get('/',[AdminController::class,"loginForm"])->name("admin.loginForm");
Route::post('/admin/auth',[AdminController::class,"auth"])->name("admin.auth");

// Admin routes with middleware admin   
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});
