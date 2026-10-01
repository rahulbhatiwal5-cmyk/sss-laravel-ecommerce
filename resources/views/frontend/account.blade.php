@extends('layouts.store')

@section('title', 'My account — sss')
@section('page-key', 'customer-account')

@section('content')
    <div class="container-wide section" data-server-account>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / My account</div>
            <h1>My account</h1>
            <p>Your saved details and everyday essentials, all in one place.</p>
        </div>

        <div class="account-layout">
            <aside class="account-nav">
                <div class="avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
                <h3>Hello, {{ $customer->name }}.</h3>
                <p class="muted">{{ $customer->email }}</p>
                <a class="active" href="{{ route('store.account') }}" aria-current="page">Overview <span>→</span></a>
                <a href="{{ route('store.profile') }}">Profile settings <span>→</span></a>
                <a href="{{ route('store.addresses') }}">My addresses <span>→</span></a>
                <a href="{{ route('store.wishlist') }}">Wishlist <span>→</span></a>
                <a href="{{ route('store.cart') }}">Shopping bag <span>→</span></a>
                <form method="POST" action="{{ route('store.logout') }}">
                    @csrf
                    <button class="text-button" type="submit">Sign out →</button>
                </form>
            </aside>

            <div>
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <section class="panel mb-4">
                    <span class="eyebrow">YOUR SSS ACCOUNT</span>
                    <h2>Welcome back, {{ $customer->name }}.</h2>
                    <p>Manage your personal details, saved delivery addresses, favourite pieces, and shopping bag.</p>
                </section>

                <div class="two-col">
                    <a class="panel" href="{{ route('store.profile') }}">
                        <h3>Profile settings →</h3>
                        <p>Update your name, phone number, or password.</p>
                    </a>
                    <a class="panel" href="{{ route('store.addresses') }}">
                        <h3>My addresses →</h3>
                        <p>Keep delivery details ready for checkout.</p>
                    </a>
                    <a class="panel" href="{{ route('store.wishlist') }}">
                        <h3>Saved pieces →</h3>
                        <p>Return to the products you have saved.</p>
                    </a>
                    <a class="panel" href="{{ route('store.cart') }}">
                        <h3>Shopping bag →</h3>
                        <p>Review the pieces currently in your bag.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
