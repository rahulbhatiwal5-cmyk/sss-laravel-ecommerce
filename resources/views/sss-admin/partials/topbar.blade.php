<header class="admin-topbar">
    <div class="topbar-left"><button id="sidebar-toggle" class="icon-button mobile-menu" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open navigation"><x-sss-admin.icon name="menu" /></button><span class="topbar-breadcrumb">Workspace <span>/</span> <strong>@yield('title', 'Dashboard')</strong></span></div>
    <div class="topbar-right">
        <span class="preview-label"><i></i> @yield('topbar-label', 'Layout preview · sample data')</span>
        <a class="store-link" href="{{ url('/') }}">View store <x-sss-admin.icon name="arrow" /></a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="text-link" type="submit">Sign out</button>
        </form>
        <span class="small muted">{{ auth()->user()->name }}</span>
        <a class="profile-avatar" href="{{ route('admin.settings') }}" aria-label="Store settings for {{ auth()->user()->name }}" title="{{ auth()->user()->name }}">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</a>
    </div>
</header>
