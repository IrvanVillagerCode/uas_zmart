<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FirebaseAuthController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [HomeController::class, 'index'])->name('landing');

// Cart Routes
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
});

// Checkout Routes (Requires authentication middleware check in controller)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

// Auth View Routes
Route::get('/login', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' ? redirect()->route('admin.dashboard') : redirect()->route('user.dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' ? redirect()->route('admin.dashboard') : redirect()->route('user.dashboard');
    }
    return view('auth.register');
})->name('register');

// Firebase Auth API Sync Routes
Route::post('/login-sync', [FirebaseAuthController::class, 'loginSync'])->name('login.sync');
Route::post('/register-sync', [FirebaseAuthController::class, 'registerSync'])->name('register.sync');
Route::post('/google-sync', [FirebaseAuthController::class, 'googleSync'])->name('google.sync');
Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout');
Route::get('/logout', [FirebaseAuthController::class, 'logout']); // Fallback GET logout for easy redirection

// Dashboards & Profile (Protected by role verification inside controllers)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('user.profile.update');
Route::post('/dashboard/password', [DashboardController::class, 'updatePassword'])->name('user.password.update');
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

// Invoice Routes
Route::get('/orders/{id}/invoice', [DashboardController::class, 'showInvoice'])->name('user.order.invoice');

// Admin CRUD & Order Management
Route::prefix('admin')->group(function () {
    Route::post('/products/store', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::post('/products/update/{id}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::post('/products/delete/{id}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');
    Route::post('/orders/status/{id}', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
});
