@extends('layouts.store')

@section('title', 'Profile settings — sss')
@section('page-key', 'customer-profile')

@section('content')
    @php
        $oldName = old('name', $customer->name);
        $nameValue = is_scalar($oldName) ? (string) $oldName : (string) $customer->name;
        $oldPhone = old('phone', $customer->phone);
        $phoneValue = is_scalar($oldPhone) ? (string) $oldPhone : '';
    @endphp

    <div class="container-wide section" data-server-profile>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / <a href="{{ route('store.account') }}">My account</a> / Profile settings</div>
            <h1>Profile settings</h1>
            <p>Keep your personal details current and your account secure.</p>
        </div>

        <div class="account-layout">
            <aside class="account-nav">
                <div class="avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
                <h3>Hello, {{ $customer->name }}.</h3>
                <p class="muted">{{ $customer->email }}</p>
                <a href="{{ route('store.account') }}">Overview <span>→</span></a>
                <a class="active" href="{{ route('store.profile') }}" aria-current="page">Profile settings <span>→</span></a>
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

                <form method="POST" action="{{ route('store.profile.update') }}" class="panel mb-4" data-server-profile-form>
                    @csrf
                    @method('PUT')

                    <h2>Your details</h2>
                    <div class="field">
                        <label for="profile-name">Full name</label>
                        <input class="form-control @error('name') is-invalid @enderror" id="profile-name" name="name" type="text"
                            value="{{ $nameValue }}" maxlength="255" required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="profile-email">Email address</label>
                        <input class="form-control" id="profile-email" type="email" value="{{ $customer->email }}" readonly autocomplete="email">
                        <p class="small muted mb-0">Email changes are not available here yet.</p>
                    </div>

                    <div class="field">
                        <label for="profile-phone">Phone number <span class="muted">(optional)</span></label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="profile-phone" name="phone" type="tel"
                            value="{{ $phoneValue }}" maxlength="20" autocomplete="tel">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-dark" type="submit">Save changes →</button>
                </form>

                <form method="POST" action="{{ route('store.profile.password.update') }}" class="panel" data-server-password-form>
                    @csrf
                    @method('PUT')

                    <h2>Change password</h2>
                    <p class="muted">Use your current password to choose a new one.</p>

                    <div class="field">
                        <label for="current-password">Current password</label>
                        <input class="form-control @error('current_password') is-invalid @enderror" id="current-password" name="current_password"
                            type="password" required autocomplete="current-password">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="new-password">New password</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="new-password" name="password" type="password"
                            required minlength="8" autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="new-password-confirmation">Confirm new password</label>
                        <input class="form-control" id="new-password-confirmation" name="password_confirmation" type="password"
                            required minlength="8" autocomplete="new-password">
                    </div>

                    <button class="btn btn-dark" type="submit">Change password →</button>
                </form>
            </div>
        </div>
    </div>
@endsection
