@extends('layouts.sss-admin')

@section('title', 'Add category')
@section('subtitle', 'Create a collection your customers can understand at a glance.')
@section('topbar-label', 'Live category data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.categories.index') }}">← All categories</a>
@endsection

@section('content')
    @include('sss-admin.categories.partials.form', [
        'category' => null,
        'parentCategories' => $parentCategories,
        'formAction' => route('admin.categories.store'),
        'formMethod' => 'POST',
        'formHeading' => 'Category details',
        'formDescription' => 'Name, describe, and optionally place this category below an existing parent.',
        'submitLabel' => 'Create category',
    ])
@endsection
