<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · sss Admin</title>
    <link rel="icon" href="{{ asset('sss-admin/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('sss-admin/css/admin.css') }}">
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#admin-main">Skip to content</a>
    @include('sss-admin.partials.sidebar')
    <button class="sidebar-overlay" id="sidebar-overlay" aria-label="Close navigation" hidden></button>
    <div class="admin-shell">
        @include('sss-admin.partials.topbar')
        <main id="admin-main" class="admin-content">
            <div class="page-heading">
                <div><div class="eyebrow">YOUR STORE, AT A GLANCE</div><h1>@yield('title', 'Dashboard')</h1><p>@yield('subtitle', 'A little clarity for your everyday business.')</p></div>
                <div class="page-actions">@yield('actions')</div>
            </div>
            @if (session('status'))
                <div class="notice" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice danger" role="alert"><strong>Please check the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
        @include('sss-admin.partials.footer')
    </div>
    <div id="admin-toast" role="status" aria-live="polite"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('sss-admin/js/admin.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
