@extends('layouts.sss-admin')

@section('title', 'Brands')
@section('subtitle', 'Maintain the brands that can be assigned to products.')
@section('topbar-label', 'Live brand data')

@section('actions')
    <a class="btn btn-dark" href="{{ route('admin.brands.create') }}">
        <x-sss-admin.icon name="plus" /> Add brand
    </a>
@endsection

@section('content')
    <section class="panel table-panel">
        <form class="table-toolbar" method="GET" action="{{ route('admin.brands.index') }}">
            <label class="search-input" for="brand-search">
                <x-sss-admin.icon name="search" />
                <input id="brand-search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search brands or slugs" aria-label="Search brands or slugs">
            </label>

            <select class="form-select" name="status" aria-label="Filter brand visibility">
                <option value="">All visibility</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button class="btn btn-light" type="submit">Filter</button>

            @if ($search !== '' || $status !== null)
                <a class="text-link" href="{{ route('admin.brands.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Logo reference</th>
                        <th>Products</th>
                        <th>Visibility</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td>
                                <strong>{{ $brand->name }}</strong>
                                @if ($brand->description)
                                    <small>{{ \Illuminate\Support\Str::limit($brand->description, 80) }}</small>
                                @endif
                            </td>
                            <td>{{ $brand->slug }}</td>
                            <td>{{ $brand->logo ?: '—' }}</td>
                            <td>
                                {{ $brand->products_count }}
                                @if ($brand->products_count > 0)
                                    <small>Assigned to products</small>
                                @endif
                            </td>
                            <td>
                                <span class="status {{ $brand->is_active ? 'status-active' : 'status-draft' }}">
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="page-actions">
                                    <a class="text-link" href="{{ route('admin.brands.edit', $brand) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}"
                                        onsubmit="return confirm('Delete this unused brand? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-link" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="6">
                                {{ $search !== '' || $status !== null ? 'No brands match these filters.' : 'No brands yet. Add one to assign it to products.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($brands->total() > 0)
            <div class="table-foot">
                <span>
                    Showing {{ $brands->firstItem() }}&ndash;{{ $brands->lastItem() }} of {{ $brands->total() }} brands
                </span>
                {{ $brands->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
