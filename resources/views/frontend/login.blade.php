@extends('layouts.store')

@section('title', 'Welcome back — sss')
@section('page-key', 'login')

@section('content')
    @php
        $oldEmail = old('email');
        $emailValue = is_scalar($oldEmail) ? (string) $oldEmail : '';
    @endphp

    <div class="container-wide section">
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / Welcome back</div>
            <h1>Welcome back</h1>
        </div>

        <div class="auth-card">
            <span class="eyebrow">YOUR WORLD OF SSS</span>
            <h2>Good to see you again.</h2>
            <p class="muted">Sign in to keep your everyday in one place.</p>

            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('store.login.submit') }}" data-server-login-form>
                @csrf

                <div class="field">
                    <label for="login-email">Email address</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="login-email" name="email" type="email"
                        value="{{ $emailValue }}" required autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="login-password">Password</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="login-password" name="password" type="password"
                        required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <label class="small"><input name="remember" type="checkbox" value="1" @checked(old('remember'))> Remember me</label>
                    <a href="{{ route('store.forgot-password') }}">Forgot password?</a>
                </div>

                <button class="btn btn-dark w-100" type="submit">Sign in</button>
            </form>

            <p class="mt-4">New here? <a href="{{ route('store.register') }}">Create an account</a></p>
        </div>
    </div>
@endsection
