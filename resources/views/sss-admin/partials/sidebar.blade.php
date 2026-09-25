<aside id="admin-sidebar" class="admin-sidebar" aria-label="Admin navigation">
    <a href="{{ route('admin.dashboard') }}" class="brand">sss<span>®</span></a>
    <div class="workspace-label"><span class="store-mark">S</span>
        <div>sss Store<small>Store management</small></div><span class="workspace-dot"></span>
    </div>
    <span class="nav-label">WORKSPACE</span>
    <nav>
        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif><x-sss-admin.icon
                name="dashboard" /><span>Overview</span></a>
        <a href="{{ route('admin.products.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.products.*')) aria-current="page" @endif><x-sss-admin.icon
                name="products" /><span>Products</span></a>
        <a href="{{ route('admin.categories.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif><x-sss-admin.icon
                name="categories" /><span>Categories</span></a>
        <a href="{{ route('admin.orders.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.orders.*')) aria-current="page" @endif><x-sss-admin.icon
                name="orders" /><span>Orders</span><span class="nav-count">6</span></a>
        <a href="{{ route('admin.customers.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.customers.*')) aria-current="page" @endif><x-sss-admin.icon
                name="customers" /><span>Customers</span></a>
        <a href="{{ route('admin.settings') }}"
            class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
            @if (request()->routeIs('admin.settings')) aria-current="page" @endif><x-sss-admin.icon
                name="settings" /><span>Settings</span></a>
    </nav>
    <div class="sidebar-bottom">
        <div class="store-card"><span class="eyebrow">THE CUSTOMER EXPERIENCE</span>
            <h3>Take a look outside.</h3>
            <p>See your store the way your customers do.</p><a href="{{ url('/') }}">Visit storefront
                <x-sss-admin.icon name="arrow" /></a>
        </div>
        <div class="sidebar-foot">sss / ADMIN WORKSPACE <span>01</span></div>
    </div>
</aside>
