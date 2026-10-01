<div class="announcement">A little refresh for your everyday. <a href="shop.html?collection=new">Explore new arrivals →</a></div>

@php($storeUser = auth('web')->user())

<header class="site-header">
    <nav class="container-wide navbar" aria-label="Main navigation">
        <a class="brand" href="index.html" aria-label="sss home">sss<span>®</span></a>
        <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>
        <div class="nav-links">
            <a href="shop.html?category=Women">Women</a>
            <a href="shop.html?category=Men">Men</a>
            <a href="shop.html?category=Kids">Kids</a>
            <a href="shop.html?category=Accessories">Accessories</a>
            <a href="shop.html?collection=new">New arrivals</a>
            <a class="sale-link" href="sale.html">Sale</a>
        </div>
        <div class="nav-actions">
            <a href="search.html" aria-label="Search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5" /></svg></a>
            @if ($storeUser?->role === 'customer')
                <a href="{{ route('store.account') }}" aria-label="Account"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4" /><path d="M4 21v-2a8 8 0 0116 0v2" /></svg><span>Account</span></a>
                <form method="POST" action="{{ route('store.logout') }}" class="d-inline">
                    @csrf
                    <button class="text-button" type="submit">Sign out</button>
                </form>
            @elseif ($storeUser?->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" aria-label="Admin dashboard"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4" /><path d="M4 21v-2a8 8 0 0116 0v2" /></svg><span>Admin</span></a>
            @else
                <a href="{{ route('store.login') }}" aria-label="Sign in"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4" /><path d="M4 21v-2a8 8 0 0116 0v2" /></svg><span>Sign in</span></a>
            @endif
            @if ($canManageWishlist ?? false)
                <a href="{{ route('store.wishlist') }}" aria-label="Wishlist"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" /></svg><span data-server-wishlist-count>{{ $wishlistCount ?? 0 }}</span></a>
            @elseif (! $storeUser)
                <a href="{{ route('store.login') }}" aria-label="Sign in to save items"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" /></svg></a>
            @endif
            <a href="{{ route('store.cart') }}" aria-label="Shopping bag"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14l1 14H4L5 7Z" /><path d="M8 8V6a4 4 0 018 0v2" /></svg><span data-cart-count data-server-cart-count>{{ $cartItemCount ?? 0 }}</span></a>
        </div>
    </nav>
</header>
