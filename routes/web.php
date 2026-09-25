<?php

use Illuminate\Support\Facades\Route;

$storeViews = [
    '404' => 'frontend.404',
    'about' => 'frontend.about',
    'account' => 'frontend.account',
    'addresses' => 'frontend.addresses',
    'article' => 'frontend.article',
    'cart' => 'frontend.cart',
    'checkout' => 'frontend.checkout',
    'collections' => 'frontend.collections',
    'contact' => 'frontend.contact',
    'faq' => 'frontend.faq',
    'forgot-password' => 'frontend.forgot-password',
    'journal' => 'frontend.journal',
    'login' => 'frontend.login',
    'order-details' => 'frontend.order-details',
    'orders' => 'frontend.orders',
    'order-success' => 'frontend.order-success',
    'privacy' => 'frontend.privacy',
    'product' => 'frontend.product',
    'profile' => 'frontend.profile',
    'register' => 'frontend.register',
    'sale' => 'frontend.sale',
    'search' => 'frontend.search',
    'shipping-returns' => 'frontend.shipping-returns',
    'shop' => 'frontend.shop',
    'sitemap' => 'frontend.sitemap',
    'size-guide' => 'frontend.size-guide',
    'terms' => 'frontend.terms',
    'track-order' => 'frontend.track-order',
    'wishlist' => 'frontend.wishlist',
];

Route::view('/', 'frontend.home')->name('store.home');

foreach ($storeViews as $uri => $view) {
    Route::view("/{$uri}", $view)->name("store.{$uri}");
}

// Preserve the template's existing .html navigation while pages move to Laravel URLs.
Route::view('/index.html', 'frontend.home');

foreach ($storeViews as $uri => $view) {
    Route::view("/{$uri}.html", $view);
}
require __DIR__.'/sss-admin-layouts.php';