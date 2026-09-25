@extends('layouts.store')

@section('title', 'My orders — sss')
@section('page-key', 'orders')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / My account</div><h1>My account</h1><p>A space for your orders, favourites, and details.</p></div><div class="account-layout"><aside class="account-nav"><div class="avatar">S</div><h3>Hello, style lover.</h3><p class="muted">Your everyday, organized.</p><a class="" href="account.html">Overview <span>↗</span></a><a class="active" href="orders.html">My orders <span>↗</span></a><a class="" href="addresses.html">My addresses <span>↗</span></a><a class="" href="profile.html">Profile settings <span>↗</span></a><a class="" href="wishlist.html">Wishlist <span>↗</span></a><a href="login.html">Sign in / switch account ↗</a></aside><div><h2>Your orders</h2><div id="orders-list"></div></div></div></div>
@endsection

