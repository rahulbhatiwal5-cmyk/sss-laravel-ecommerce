@extends('layouts.sss-admin')

@section('title', 'Categories')
@section('subtitle', 'Organise your store into clear, useful collections.')
@section('topbar-label', 'Live category data')

@section('actions')
    <a class="btn btn-dark" href="{{ route('admin.categories.create') }}">
        <x-sss-admin.icon name="plus" /> Add category
    </a>
@endsection

@section('content')
    <section class="panel table-panel">
        <form class="table-toolbar" method="GET" action="{{ route('admin.categories.index') }}">
            <label class="search-input" for="category-search">
                <x-sss-admin.icon name="search" />
                <input id="category-search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search name or slug" aria-label="Search categories">
            </label>

            <select class="form-select" name="status" aria-label="Filter category visibility">
                <option value="">All visibility</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button class="btn btn-light" type="submit">Filter</button>

            @if ($search !== '' || $status !== null)
                <a class="text-link" href="{{ route('admin.categories.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Parent</th>
                        <th>Products</th>
                        <th>Visibility</th>
                        <th>Sort order</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                <small>{{ $category->slug }}</small>
                            </td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                <span class="status {{ $category->is_active ? 'status-active' : 'status-draft' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <div class="page-actions">
                                    <a class="text-link" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        onsubmit="return confirm('Delete this empty category? This cannot be undone.')">
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
                                {{ $search !== '' || $status !== null ? 'No categories match these filters.' : 'No categories yet. Add your first category to begin.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->total() > 0)
            <div class="table-foot">
                <span>
                    Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }} categories
                </span>
                {{ $categories->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
