@extends('layouts.sss-admin')
@section('title', 'Categories')
@section('subtitle', 'Give every piece a place to belong.')
@section('actions')

@endsection
@section('content')
<div class="form-layout">
    <section class="panel table-panel">
        <div class="panel-heading">
            <h2>Your collections</h2><span class="muted small">4 collections</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Collection</th>
                        <th>Products</th>
                        <th>Visibility</th>
                    </tr>
                </thead>
                <tbody>@foreach(['Women', 'Men', 'Kids', 'Accessories'] as $category)<tr>
                        <td><strong>{{ $category }}</strong><small>{{ strtolower($category) }}</small></td>
                        <td>{{ collect(config('sss-admin-demo.products'))->where('category', $category)->count() }}</td>
                        <td><span class="status status-active">Active</span></td>
                    </tr>@endforeach</tbody>
            </table>
        </div>
    </section>
    <form onsubmit="return false" class="panel" data-preview-form="Category details validated. No category was created.">
        <h2>Add a collection</h2>
        <div class="field"><label for="category-name">Name <span>*</span></label><input class="form-control" id="category-name" placeholder="e.g. Summer essentials" required></div>
        <div class="field"><label for="category-description">Description</label><textarea class="form-control" id="category-description" rows="4" placeholder="A short introduction to the collection…"></textarea></div><button class="btn btn-dark w-100">Preview collection ↗</button>
    </form>
</div>
@endsection