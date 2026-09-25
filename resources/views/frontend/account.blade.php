@extends('layouts.store')

@section('title', 'My account — sss')
@section('page-key', 'account')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / My account</div><h1>My account</h1><p>A space for your orders, favourites, and details.</p></div><div class="account-layout"><aside class="account-nav"><div class="avatar">S</div><h3>Hello, style lover.</h3><p class="muted">Your everyday, organized.</p><a class="active" href="account.html">Overview <span>↗</span></a><a class="" href="orders.html">My orders <span>↗</span></a><a class="" href="addresses.html">My addresses <span>↗</span></a><a class="" href="profile.html">Profile settings <span>↗</span></a><a class="" href="wishlist.html">Wishlist <span>↗</span></a><a href="login.html">Sign in / switch account ↗</a></aside><div><div class="panel"><span class="eyebrow">THE EVERYDAY DASHBOARD</span><h2>Make yourself at home.</h2><p>Keep an eye on your orders, save your favourites, and update your details.</p><p class="small muted">Sample account preview. Authentication will be handled by your Laravel application.</p></div><div class="two-col"><a class="panel" href="orders.html"><h3>Your orders ↗</h3><p>See what’s on its way.</p></a><a class="panel" href="wishlist.html"><h3>Saved pieces ↗</h3><p>Pick up where you left off.</p></a></div><div class="panel"><h3>A wardrobe refresh?</h3><p>Meet the pieces just added to the collection.</p><a class="btn btn-dark" href="shop.html?collection=new">See what’s new ↗</a></div></div></div></div>
@endsection

