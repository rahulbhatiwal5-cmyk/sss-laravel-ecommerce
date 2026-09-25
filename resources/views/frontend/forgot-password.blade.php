@extends('layouts.store')

@section('title', 'Reset your password — sss')
@section('page-key', 'forgot-password')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / Let’s get you back in.</div><h1>Let’s get you back in.</h1><p></p></div><div class="auth-card"><h2>Forgot your password?</h2><p>Enter the email associated with your account.</p><form data-demo="Password reset preview. Connect Laravel’s password reset notification to send a real email."><div class="field"><label for="reset-email">Email address</label><input class="form-control" id="reset-email" name="reset-email" type="email" value="" required ></div><button class="btn btn-dark w-100">Send reset link ↗</button></form><a class="text-link mt-4 d-inline-block" href="login.html">← Back to sign in</a></div></div>
@endsection

