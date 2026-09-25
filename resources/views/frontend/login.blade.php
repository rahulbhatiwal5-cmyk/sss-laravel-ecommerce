@extends('layouts.store')

@section('title', 'Welcome back — sss')
@section('page-key', 'login')

@section('content')
    <div class="container-wide section">
        <div class="page-heading">
            <div class="crumb"><a href="index.html">Home</a> / Welcome back</div>
            <h1>Welcome back</h1>
            <p></p>
        </div>
        <div class="auth-card"><span class="eyebrow">YOUR WORLD OF SSS</span>
            <h2>Good to see you again.</h2>
            <p class="muted">Sign in to keep your everyday in one place.</p>
            <form data-demo="Sign-in UI preview. Connect this form to Laravel authentication.">
                <div class="field"><label for="login-email">Email address</label><input class="form-control" id="login-email"
                        name="login-email" type="email" value="" required autocomplete="username"></div>
                <div class="field"><label for="login-password">Password</label><input class="form-control"
                        id="login-password" name="login-password" type="password" value="" required
                        autocomplete="current-password" minlength="8"></div>
                <div class="d-flex justify-content-between mb-4"><label><input type="checkbox"> Remember me</label><a
                        href="forgot-password.html">Forgot password?</a></div><button class="btn btn-dark w-100">Sign in
                    ↗</button>
            </form>
            <p class="mt-4">New here? <a href="register.html">Create an account</a></p>
        </div>
    </div>
@endsection
