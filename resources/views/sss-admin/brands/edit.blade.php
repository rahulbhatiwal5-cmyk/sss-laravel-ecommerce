@extends('layouts.sss-admin')

@section('title', 'Edit brand')
@section('subtitle', 'Update this brand without changing assigned products.')
@section('topbar-label', 'Live brand data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.brands.index') }}">&larr; All brands</a>
@endsection

@section('content')
    @include('sss-admin.brands.partials.form', [
        'brand' => $brand,
        'formAction' => route('admin.brands.update', $brand),
        'formMethod' => 'PUT',
        'formHeading' => 'Brand details',
        'formDescription' => 'Update the brand record without changing products that already use it.',
        'submitLabel' => 'Save brand',
    ])
@endsection
