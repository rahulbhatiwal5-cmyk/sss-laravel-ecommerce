@extends('layouts.sss-admin')

@section('title', 'Edit size')
@section('subtitle', 'Update this size label, its listing order, or its visibility.')
@section('topbar-label', 'Live size data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.sizes.index') }}">&larr; All sizes</a>
@endsection

@section('content')
    @include('sss-admin.sizes.partials.form', [
        'size' => $size,
        'formAction' => route('admin.sizes.update', $size),
        'formMethod' => 'PUT',
        'formHeading' => 'Size details',
        'formDescription' => 'Update the label without changing any product variants that already use it.',
        'submitLabel' => 'Save size',
    ])
@endsection
