@extends('layouts.store')

@section('title', 'Join the everyday — sss')
@section('page-key', 'register')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / Join the everyday</div><h1>Join the everyday</h1><p></p></div><div class="auth-card"><h2>A little more you.</h2><form id="register-form"><div class="field"><label for="register-name">Full name</label><input class="form-control" id="register-name" name="register-name" type="text" value="" required autocomplete="name"></div><div class="field"><label for="register-email">Email address</label><input class="form-control" id="register-email" name="register-email" type="email" value="" required autocomplete="email"></div><div class="field"><label for="register-password">Password</label><input class="form-control" id="register-password" name="register-password" type="password" value="" required minlength="8" autocomplete="new-password"></div><div class="field"><label for="confirm-password">Confirm password</label><input class="form-control" id="confirm-password" name="confirm-password" type="password" value="" required minlength="8" autocomplete="new-password"></div><label class="check"><input type="checkbox" required> I agree to the <a href="terms.html">terms</a> and <a href="privacy.html">privacy policy</a>.</label><button class="btn btn-dark w-100">Create account ↗</button></form><p class="mt-4">Already a member? <a href="login.html">Sign in</a></p></div></div>
@endsection

