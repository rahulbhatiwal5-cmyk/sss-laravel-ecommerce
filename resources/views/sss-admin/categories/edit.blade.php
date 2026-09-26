@extends('layouts.sss-admin')

@section('title', 'Edit category')
@section('subtitle', 'Refine the details and placement of this collection.')
@section('topbar-label', 'Live category data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.categories.index') }}">← All categories</a>
@endsection

@section('content')
    @include('sss-admin.categories.partials.form', [
        'category' => $category,
        'parentCategories' => $parentCategories,
        'formAction' => route('admin.categories.update', $category),
        'formMethod' => 'PUT',
        'formHeading' => 'Category details',
        'formDescription' => 'Update this category without creating an invalid hierarchy.',
        'submitLabel' => 'Save category',
    ])
@endsection
