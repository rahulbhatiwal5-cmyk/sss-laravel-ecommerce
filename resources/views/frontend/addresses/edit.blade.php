@extends('layouts.store')

@section('title', 'Edit address — sss')
@section('page-key', 'customer-addresses')

@section('content')
    <div class="container-wide section" data-server-addresses>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / <a href="{{ route('store.addresses') }}">My addresses</a> / Edit</div>
            <h1>Edit delivery address</h1>
            <p>Update only this saved address.</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @include('frontend.addresses._form', [
            'address' => $address,
            'formAction' => route('store.addresses.update', $address),
            'formMethod' => 'PUT',
            'isFirstAddress' => $isFirstAddress,
            'heading' => 'Update address',
        ])
    </div>
@endsection
