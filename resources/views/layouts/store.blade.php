<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="sss storefront">
    <title>@yield('title', 'sss')</title>
    <link rel="icon" href="{{ asset('frontend/assets/images/favicon.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('frontend/assets/css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body data-page="@yield('page-key', 'index')">
    <a class="skip" href="#main">Skip to content</a>
    @include('partials.store-header')
    <main id="main">
        @yield('content')
    </main>
    @include('partials.store-footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontend/assets/js/products.js') }}"></script>
    <script>window.storeImageBase = @json(asset('frontend/assets/images'));</script>
    <script src="{{ asset('frontend/assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
