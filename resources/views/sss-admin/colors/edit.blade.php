@extends('layouts.sss-admin')

@section('title', 'Edit color')
@section('subtitle', 'Update this color option without changing product variants that already use it.')
@section('topbar-label', 'Live color data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.colors.index') }}">&larr; All colors</a>
@endsection

@section('content')
    @include('sss-admin.colors.partials.form', [
        'color' => $color,
        'formAction' => route('admin.colors.update', $color),
        'formMethod' => 'PUT',
        'formHeading' => 'Color details',
        'formDescription' => 'Update the name, code, or visibility without changing related product variants.',
        'submitLabel' => 'Save color',
    ])
@endsection
