<?php

namespace App\Providers;

use App\Services\GuestCartManager;
use App\Services\CustomerWishlist;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.store-header', function ($view): void {
            $wishlists = app(CustomerWishlist::class);
            $wishlistCustomer = $wishlists->activeCustomer();

            $view->with([
                'cartItemCount' => app(GuestCartManager::class)->itemCount(),
                'canManageWishlist' => $wishlistCustomer !== null,
                'wishlistCount' => $wishlists->countFor($wishlistCustomer),
            ]);
        });
    }
}
