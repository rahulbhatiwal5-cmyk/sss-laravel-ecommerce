@extends('layouts.sss-admin')

@section('title', 'Add brand')
@section('subtitle', 'Create a brand for product assignment.')
@section('topbar-label', 'Live brand data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.brands.index') }}">&larr; All brands</a>
@endsection

@section('content')
    @include('sss-admin.brands.partials.form', [
        'brand' => null,
        'formAction' => route('admin.brands.store'),
        'formMethod' => 'POST',
        'formHeading' => 'Brand details',
        'formDescription' => 'Use a clear name and an optional logo reference for this brand.',
        'submitLabel' => 'Create brand',
    ])
@endsection
