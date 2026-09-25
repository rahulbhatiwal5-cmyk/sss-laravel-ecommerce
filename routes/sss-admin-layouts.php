<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\SssAdminLayoutPreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    // These pages still use sample fixtures, but access is now limited to active admins.
    Route::middleware('admin')->group(function () {
        Route::view('/', 'sss-admin.dashboard')->name('dashboard');
        Route::view('/products', 'sss-admin.products.index')->name('products.index');
        Route::view('/products/create', 'sss-admin.products.form')->name('products.create');
        Route::get('/products/{product}/edit', [SssAdminLayoutPreviewController::class, 'editProduct'])->whereNumber('product')->name('products.edit');
        Route::view('/categories', 'sss-admin.categories')->name('categories.index');
        Route::view('/orders', 'sss-admin.orders.index')->name('orders.index');
        Route::get('/orders/{order}', [SssAdminLayoutPreviewController::class, 'showOrder'])->name('orders.show');
        Route::view('/customers', 'sss-admin.customers')->name('customers.index');
        Route::view('/settings', 'sss-admin.settings')->name('settings');
    });
});
