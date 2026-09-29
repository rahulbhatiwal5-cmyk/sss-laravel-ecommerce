<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CustomerAuthController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$storeViews = [
    '404' => 'frontend.404',
    'about' => 'frontend.about',
    'account' => 'frontend.account',
    'addresses' => 'frontend.addresses',
    'article' => 'frontend.article',
    'checkout' => 'frontend.checkout',
    'collections' => 'frontend.collections',
    'contact' => 'frontend.contact',
    'faq' => 'frontend.faq',
    'forgot-password' => 'frontend.forgot-password',
    'journal' => 'frontend.journal',
    'order-details' => 'frontend.order-details',
    'orders' => 'frontend.orders',
    'order-success' => 'frontend.order-success',
    'privacy' => 'frontend.privacy',
    'product' => 'frontend.product',
    'profile' => 'frontend.profile',
    'sale' => 'frontend.sale',
    'search' => 'frontend.search',
    'shipping-returns' => 'frontend.shipping-returns',
    'sitemap' => 'frontend.sitemap',
    'size-guide' => 'frontend.size-guide',
    'terms' => 'frontend.terms',
    'track-order' => 'frontend.track-order',
    'wishlist' => 'frontend.wishlist',
];

Route::view('/', 'frontend.home')->name('store.home');
Route::get('/shop', [ShopController::class, 'index'])->name('store.shop');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('store.products.show');
Route::get('/login', [CustomerAuthController::class, 'createLogin'])->name('store.login');
Route::post('/login', [CustomerAuthController::class, 'storeLogin'])->name('store.login.submit');
Route::get('/register', [CustomerAuthController::class, 'createRegistration'])->name('store.register');
Route::post('/register', [CustomerAuthController::class, 'storeRegistration'])->name('store.register.submit');
Route::post('/logout', [CustomerAuthController::class, 'destroy'])->name('store.logout');
Route::get('/cart', [CartController::class, 'index'])->name('store.cart');
Route::post('/products/{product:slug}/cart', [CartController::class, 'store'])
    ->block(10, 10)
    ->name('store.cart.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])
    ->whereNumber('cartItem')
    ->block(10, 10)
    ->name('store.cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])
    ->whereNumber('cartItem')
    ->block(10, 10)
    ->name('store.cart.destroy');

foreach ($storeViews as $uri => $view) {
    Route::view("/{$uri}", $view)->name("store.{$uri}");
}

// Preserve the template's existing .html navigation while pages move to Laravel URLs.
Route::view('/index.html', 'frontend.home');
Route::get('/shop.html', function (Request $request) {
    return redirect()->route('store.shop', $request->query(), 301);
});
Route::get('/cart.html', function (Request $request) {
    return redirect()->route('store.cart', $request->query(), 301);
});
Route::get('/login.html', function (Request $request) {
    return redirect()->route('store.login', $request->query(), 301);
});
Route::get('/register.html', function (Request $request) {
    return redirect()->route('store.register', $request->query(), 301);
});

foreach ($storeViews as $uri => $view) {
    Route::view("/{$uri}.html", $view);
}
require __DIR__.'/sss-admin-layouts.php';
