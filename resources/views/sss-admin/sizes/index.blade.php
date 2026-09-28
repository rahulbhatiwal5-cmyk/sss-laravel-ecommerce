@extends('layouts.sss-admin')

@section('title', 'Sizes')
@section('subtitle', 'Keep product sizing clear, ordered, and ready for variants.')
@section('topbar-label', 'Live size data')

@section('actions')
    <a class="btn btn-dark" href="{{ route('admin.sizes.create') }}">
        <x-sss-admin.icon name="plus" /> Add size
    </a>
@endsection

@section('content')
    <section class="panel table-panel">
        <form class="table-toolbar" method="GET" action="{{ route('admin.sizes.index') }}">
            <label class="search-input" for="size-search">
                <x-sss-admin.icon name="search" />
                <input id="size-search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search sizes" aria-label="Search sizes">
            </label>

            <select class="form-select" name="status" aria-label="Filter size visibility">
                <option value="">All visibility</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button class="btn btn-light" type="submit">Filter</button>

            @if ($search !== '' || $status !== null)
                <a class="text-link" href="{{ route('admin.sizes.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Variants</th>
                        <th>Visibility</th>
                        <th>Sort order</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sizes as $size)
                        <tr>
                            <td><strong>{{ $size->name }}</strong></td>
                            <td>
                                {{ $size->variants_count }}
                                @if ($size->variants_count > 0)
                                    <small>In use by product variants</small>
                                @endif
                            </td>
                            <td>
                                <span class="status {{ $size->is_active ? 'status-active' : 'status-draft' }}">
                                    {{ $size->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $size->sort_order }}</td>
                            <td>
                                <div class="page-actions">
                                    <a class="text-link" href="{{ route('admin.sizes.edit', $size) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.sizes.destroy', $size) }}"
                                        onsubmit="return confirm('Delete this unused size? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-link" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="5">
                                {{ $search !== '' || $status !== null ? 'No sizes match these filters.' : 'No sizes yet. Add S, M, and L to begin.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sizes->total() > 0)
            <div class="table-foot">
                <span>
                    Showing {{ $sizes->firstItem() }}&ndash;{{ $sizes->lastItem() }} of {{ $sizes->total() }} sizes
                </span>
                {{ $sizes->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
