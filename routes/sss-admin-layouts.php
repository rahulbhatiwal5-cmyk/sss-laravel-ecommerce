<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\SizeController;
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
        Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])->whereNumber('product')->name('products.variants.index');
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->whereNumber('product')->name('products.variants.store');
        Route::get('/products/{product}/variants/{variant}/edit', [ProductVariantController::class, 'edit'])->whereNumber('product')->whereNumber('variant')->name('products.variants.edit');
        Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->whereNumber('product')->whereNumber('variant')->name('products.variants.update');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->whereNumber('product')->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product')->name('products.update');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/sizes', [SizeController::class, 'index'])->name('sizes.index');
        Route::get('/sizes/create', [SizeController::class, 'create'])->name('sizes.create');
        Route::post('/sizes', [SizeController::class, 'store'])->name('sizes.store');
        Route::get('/sizes/{size}/edit', [SizeController::class, 'edit'])->name('sizes.edit');
        Route::put('/sizes/{size}', [SizeController::class, 'update'])->name('sizes.update');
        Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');
        Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
        Route::get('/colors/create', [ColorController::class, 'create'])->name('colors.create');
        Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
        Route::get('/colors/{color}/edit', [ColorController::class, 'edit'])->name('colors.edit');
        Route::put('/colors/{color}', [ColorController::class, 'update'])->name('colors.update');
        Route::delete('/colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::view('/orders', 'sss-admin.orders.index')->name('orders.index');
        Route::get('/orders/{order}', [SssAdminLayoutPreviewController::class, 'showOrder'])->name('orders.show');
        Route::view('/customers', 'sss-admin.customers')->name('customers.index');
        Route::view('/settings', 'sss-admin.settings')->name('settings');
    });
});
