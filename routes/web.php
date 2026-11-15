<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. SYSTEM & HEALTH CHECK
// ==========================================
Route::get('/health', [HealthController::class, 'check'])->name('health');

// ==========================================
// 2. PUBLIC - CATALOG & KONTAK
// ==========================================
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('catalog.show');

// ✅ DIPERBAIKI: Menghubungkan halaman kontak publik dengan data dari Admin
Route::get('/kontak', function () {
    $settings = \App\Models\Setting::firstWhere('key', 'outlet_info');
    $data = $settings ? (is_array($settings->value) ? $settings->value : json_decode($settings->value, true)) : [];
    return view('contact.index', compact('data'));
})->name('contact.index');

// ==========================================
// 3. CART (KERANJANG BELANJA) - PUBLIK
// ==========================================
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// ==========================================
// 4. CUSTOMER CHECKOUT FLOW (TANPA LOGIN / GUEST)
// ==========================================
Route::get('/checkout', [OrderController::class, 'create'])->name('orders.create');
Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store')->middleware('throttle:10,1');

// ✅ Memakai kode referensi acak (bukan ID berurutan) agar pesanan tidak bisa dienumerasi
Route::get('/orders/{order:order_ref}/success', function (Order $order) {
    $order->load(['promo', 'orderItems.product', 'payment']);

    // Sengaja tidak menyimpan order ini ke sesi: pengunjung yang hanya tahu kode
    // referensi tidak boleh bisa mengunggah bukti bayar untuk pesanan orang lain.
    return view('orders.success', compact('order'));
})->name('orders.success')->middleware('throttle:30,1');

// Status Tracking (Publik) — dicari lewat kode referensi, fallback ke sesi pesanan sendiri
Route::get('/status', function (Request $request) {
    $order = null;

    if ($request->filled('ref')) {
        $order = Order::with(['orderItems.product', 'promo'])
            ->where('order_ref', strtoupper(trim((string) $request->query('ref'))))
            ->first();
    } elseif ($orderId = session('last_order_id')) {
        $order = Order::with(['orderItems.product', 'promo'])->find($orderId);
    }

    return view('orders.status', compact('order'));
})->name('orders.status')->middleware('throttle:30,1');

Route::get('/status/{order:order_ref}', function (Order $order) {
    $order->load(['orderItems.product', 'promo']);
    return view('orders.status', compact('order'));
})->name('orders.status.show')->middleware('throttle:30,1');

// ==========================================
// 5. PUBLIC PAYMENT UPLOAD (KHUSUS PELANGGAN)
// ==========================================
Route::post('/payments/guest-upload', [PaymentController::class, 'storeGuest'])->name('payments.guest.store')->middleware('throttle:5,1');

// ==========================================
// 6. AUTHENTICATION (KHUSUS ADMIN/OWNER)
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// ✅ Registrasi publik ditutup: akun admin/owner hanya dibuat lewat seeder / pengelolaan internal

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ==========================================
// 7. ADMIN PANEL (WAJIB LOGIN)
// ==========================================
Route::middleware(['auth', 'role:ADMIN,OWNER'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Categories
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    // Products
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');

    // Promos
    Route::get('/promos', [AdminController::class, 'promos'])->name('promos');
    Route::post('/promos', [AdminController::class, 'storePromo'])->name('promos.store');
    Route::put('/promos/{promo}', [AdminController::class, 'updatePromo'])->name('promos.update');
    Route::delete('/promos/{promo}', [AdminController::class, 'destroyPromo'])->name('promos.destroy');

    // Orders (Admin View)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/detail', [OrderController::class, 'detail'])->name('orders.detail');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Payments (Admin View)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::get('/payments/create/{order}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // ✅ Route untuk Admin memverifikasi bukti pembayaran
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');

    // ✅ Bukti bayar disimpan di storage privat, hanya admin (login + role) yang bisa membuka
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Contact & Outlet (Admin View)
    Route::get('/contact', [AdminController::class, 'contact'])->name('contact');
    Route::post('/contact', [AdminController::class, 'updateContact'])->name('contact.update');
});
