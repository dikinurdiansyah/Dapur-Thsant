<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PaymentVerifyController;
use Illuminate\Support\Facades\Route;

// Endpoint API Publik (checkout) — dibatasi agar tidak bisa dispam
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');

// Rute khusus Admin/Owner (token Sanctum + role)
Route::middleware(['auth:sanctum', 'role:ADMIN,OWNER'])->group(function () {
    Route::post('/admin/payment/{payment}/verify', PaymentVerifyController::class);
});
