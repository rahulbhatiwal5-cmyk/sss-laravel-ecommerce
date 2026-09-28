<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
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
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->whereNumber('product')->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product')->name('products.update');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::view('/orders', 'sss-admin.orders.index')->name('orders.index');
        Route::get('/orders/{order}', [SssAdminLayoutPreviewController::class, 'showOrder'])->name('orders.show');
        Route::view('/customers', 'sss-admin.customers')->name('customers.index');
        Route::view('/settings', 'sss-admin.settings')->name('settings');
    });
});
