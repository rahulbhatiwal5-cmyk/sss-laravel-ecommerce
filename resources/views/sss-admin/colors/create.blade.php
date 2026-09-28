@extends('layouts.sss-admin')

@section('title', 'Add color')
@section('subtitle', 'Create a color option for future product variants.')
@section('topbar-label', 'Live color data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.colors.index') }}">&larr; All colors</a>
@endsection

@section('content')
    @include('sss-admin.colors.partials.form', [
        'color' => null,
        'formAction' => route('admin.colors.store'),
        'formMethod' => 'POST',
        'formHeading' => 'Color details',
        'formDescription' => 'Use a clear customer-facing name and an optional hexadecimal color code.',
        'submitLabel' => 'Create color',
    ])
@endsection
