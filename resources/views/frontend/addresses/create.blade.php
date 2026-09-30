@extends('layouts.store')

@section('title', 'Add address — sss')
@section('page-key', 'customer-addresses')

@section('content')
    <div class="container-wide section" data-server-addresses>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / <a href="{{ route('store.addresses') }}">My addresses</a> / Add</div>
            <h1>Add delivery address</h1>
            <p>Save a delivery address to your customer account.</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @include('frontend.addresses._form', [
            'address' => $address,
            'formAction' => route('store.addresses.store'),
            'formMethod' => 'POST',
            'isFirstAddress' => $isFirstAddress,
            'heading' => 'New address',
        ])
    </div>
@endsection
