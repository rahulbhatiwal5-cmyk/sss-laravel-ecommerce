@extends('layouts.store')

@section('title', 'Profile settings — sss')
@section('page-key', 'profile')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / My account</div><h1>My account</h1><p>A space for your orders, favourites, and details.</p></div><div class="account-layout"><aside class="account-nav"><div class="avatar">S</div><h3>Hello, style lover.</h3><p class="muted">Your everyday, organized.</p><a class="" href="account.html">Overview <span>↗</span></a><a class="" href="orders.html">My orders <span>↗</span></a><a class="" href="addresses.html">My addresses <span>↗</span></a><a class="active" href="profile.html">Profile settings <span>↗</span></a><a class="" href="wishlist.html">Wishlist <span>↗</span></a><a href="login.html">Sign in / switch account ↗</a></aside><div><form id="profile-form" class="panel"><h2>Your details</h2><div class="field"><label for="profile-name">Full name</label><input class="form-control" id="profile-name" name="profile-name" type="text" value="" required ></div><div class="field"><label for="profile-email">Email address</label><input class="form-control" id="profile-email" name="profile-email" type="email" value="" required ></div><div class="field"><label for="profile-phone">Phone number</label><input class="form-control" id="profile-phone" name="profile-phone" type="tel" value="" required ></div><button class="btn btn-dark">Save changes ↗</button><p class="small muted mt-3">Demo details stay in this browser only. Use sample information.</p></form></div></div></div>
@endsection

