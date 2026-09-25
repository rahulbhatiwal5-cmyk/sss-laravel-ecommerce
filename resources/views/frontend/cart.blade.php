@extends('layouts.store')

@section('title', 'Your shopping bag — sss')
@section('page-key', 'cart')

@section('content')
    <div class="container-wide section">
        <div class="page-heading">
            <div class="crumb"><a href="index.html">Home</a> / Your shopping bag</div>
            <h1>Your shopping bag</h1>
            <p>Good choices. Let’s make them yours.</p>
        </div>
        <div id="cart-content"></div>
    </div>
@endsection
