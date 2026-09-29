@extends('layouts.store')

@section('title', 'Join the everyday — sss')
@section('page-key', 'customer-register')

@section('content')
    @php
        $oldName = old('name');
        $nameValue = is_scalar($oldName) ? (string) $oldName : '';
        $oldEmail = old('email');
        $emailValue = is_scalar($oldEmail) ? (string) $oldEmail : '';
        $oldPhone = old('phone');
        $phoneValue = is_scalar($oldPhone) ? (string) $oldPhone : '';
    @endphp

    <div class="container-wide section">
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / Join the everyday</div>
            <h1>Join the everyday</h1>
        </div>

        <div class="auth-card">
            <span class="eyebrow">YOUR WORLD OF SSS</span>
            <h2>A little more you.</h2>

            <form id="register-form" method="POST" action="{{ route('store.register.submit') }}" data-server-register-form>
                @csrf

                <div class="field">
                    <label for="register-name">Full name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="register-name" name="name" type="text"
                        value="{{ $nameValue }}" required autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="register-email">Email address</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="register-email" name="email" type="email"
                        value="{{ $emailValue }}" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="register-phone">Phone number <span class="muted">(optional)</span></label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="register-phone" name="phone" type="tel"
                        value="{{ $phoneValue }}" maxlength="20" autocomplete="tel">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="register-password">Password</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="register-password" name="password" type="password"
                        required minlength="8" autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="confirm-password">Confirm password</label>
                    <input class="form-control" id="confirm-password" name="password_confirmation" type="password" required
                        minlength="8" autocomplete="new-password">
                </div>

                <label class="check">
                    <input name="terms" type="checkbox" value="1" required @checked(old('terms'))>
                    I agree to the <a href="{{ route('store.terms') }}">terms</a> and
                    <a href="{{ route('store.privacy') }}">privacy policy</a>.
                </label>
                @error('terms')
                    <p class="small text-danger">{{ $message }}</p>
                @enderror

                <button class="btn btn-dark w-100" type="submit">Create account</button>
            </form>

            <p class="mt-4">Already a member? <a href="{{ route('store.login') }}">Sign in</a></p>
        </div>
    </div>
@endsection
