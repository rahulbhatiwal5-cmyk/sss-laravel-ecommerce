<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\AddressController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\CustomerAuthController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ShopController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$storeViews = [
    '404' => 'frontend.404',
    'about' => 'frontend.about',
    'article' => 'frontend.article',
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
    'sale' => 'frontend.sale',
    'search' => 'frontend.search',
    'shipping-returns' => 'frontend.shipping-returns',
    'sitemap' => 'frontend.sitemap',
    'size-guide' => 'frontend.size-guide',
    'terms' => 'frontend.terms',
    'track-order' => 'frontend.track-order',
];

Route::view('/', 'frontend.home')->name('store.home');
Route::get('/shop', [ShopController::class, 'index'])->name('store.shop');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('store.products.show');
Route::get('/login', [CustomerAuthController::class, 'createLogin'])->name('store.login');
Route::post('/login', [CustomerAuthController::class, 'storeLogin'])->name('store.login.submit');
Route::get('/register', [CustomerAuthController::class, 'createRegistration'])->name('store.register');
Route::post('/register', [CustomerAuthController::class, 'storeRegistration'])->name('store.register.submit');
Route::post('/logout', [CustomerAuthController::class, 'destroy'])->name('store.logout');
Route::middleware('customer')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('store.account');
    Route::get('/profile', [AccountController::class, 'edit'])->name('store.profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('store.profile.update');
    Route::put('/profile/password', [AccountController::class, 'updatePassword'])->name('store.profile.password.update');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('store.checkout');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('store.wishlist');
    Route::post('/products/{product:slug}/wishlist', [WishlistController::class, 'store'])->name('store.wishlist.store');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])
        ->whereNumber('wishlist')
        ->name('store.wishlist.destroy');
    Route::get('/addresses', [AddressController::class, 'index'])->name('store.addresses');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('store.addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('store.addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])
        ->whereNumber('address')
        ->name('store.addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])
        ->whereNumber('address')
        ->name('store.addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])
        ->whereNumber('address')
        ->name('store.addresses.destroy');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])
        ->whereNumber('address')
        ->name('store.addresses.default');
});
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
Route::get('/checkout.html', function (Request $request) {
    return redirect()->route('store.checkout', $request->query(), 301);
});
Route::get('/wishlist.html', function (Request $request) {
    return redirect()->route('store.wishlist', $request->query(), 301);
});
Route::get('/addresses.html', function (Request $request) {
    return redirect()->route('store.addresses', $request->query(), 301);
});
Route::get('/account.html', function (Request $request) {
    return redirect()->route('store.account', $request->query(), 301);
});
Route::get('/profile.html', function (Request $request) {
    return redirect()->route('store.profile', $request->query(), 301);
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
