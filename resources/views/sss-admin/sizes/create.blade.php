@extends('layouts.sss-admin')

@section('title', 'Add size')
@section('subtitle', 'Create a size option for future product variants.')
@section('topbar-label', 'Live size data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.sizes.index') }}">&larr; All sizes</a>
@endsection

@section('content')
    @include('sss-admin.sizes.partials.form', [
        'size' => null,
        'formAction' => route('admin.sizes.store'),
        'formMethod' => 'POST',
        'formHeading' => 'Size details',
        'formDescription' => 'Use a short, clear label such as S, M, L, or One size.',
        'submitLabel' => 'Create size',
    ])
@endsection
