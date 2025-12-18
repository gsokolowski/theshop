<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

// General route to acction login 
Route::get('/',[AdminController::class,"login"])->name("admin.login");
Route::post('/admin/auth',[AdminController::class,"auth"])->name("admin.auth");

// Admin routes with middleware admin  Url, Controller, Name of the route
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});
