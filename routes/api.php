<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Endpoint API Publik (checkout) — dibatasi agar tidak bisa dispam
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');

// Rute khusus Admin
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/payment/{payment}/verify', [PaymentController::class, 'verify']);
});
