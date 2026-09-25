<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin sign in · sss</title>
    <link rel="icon" href="{{ asset('sss-admin/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('sss-admin/css/admin.css') }}">
    <style>
        .admin-login-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .admin-login-shell { width: min(100%, 430px); }
        .admin-login-shell .brand { margin: 0 0 32px; text-align: center; }
        .admin-login-card { padding: 32px; }
        .admin-login-card h1 { margin-bottom: 10px; }
        .admin-login-card > p { color: var(--muted); font-size: 12px; margin-bottom: 28px; }
        .admin-login-card .check { display: flex; align-items: center; gap: 8px; margin: 0 0 22px; font-size: 11px; }
        .admin-login-card .field-error { color: #9f756d; font-size: 10px; margin: 7px 0 0; }
        .admin-login-foot { color: var(--muted); font-size: 10px; text-align: center; margin: 20px 0 0; }
    </style>
</head>

<body class="admin-login-page">
    <a class="skip-link" href="#admin-login">Skip to sign in</a>

    <main id="admin-login" class="admin-login-shell">
        <a href="{{ url('/') }}" class="brand" aria-label="sss storefront">sss<span>®</span></a>

        <section class="panel admin-login-card" aria-labelledby="admin-login-title">
            <span class="eyebrow">ADMIN WORKSPACE</span>
            <h1 id="admin-login-title">Welcome back.</h1>
            <p>Sign in to manage the sss store.</p>

            @if (session('status'))
                <div class="notice" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <div class="field">
                    <label for="email">Email address</label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    >
                    @error('email')
                        <p class="field-error" id="email-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        class="form-control"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    >
                    @error('password')
                        <p class="field-error" id="password-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="check" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    Remember me on this device
                </label>

                <button class="btn btn-dark w-100" type="submit">Sign in</button>
            </form>
        </section>

        <p class="admin-login-foot">Secure access for active sss administrators only.</p>
    </main>
</body>

</html>
