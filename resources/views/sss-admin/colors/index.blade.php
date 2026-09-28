@extends('layouts.sss-admin')

@section('title', 'Colors')
@section('subtitle', 'Maintain the color options available for future product variants.')
@section('topbar-label', 'Live color data')

@section('actions')
    <a class="btn btn-dark" href="{{ route('admin.colors.create') }}">
        <x-sss-admin.icon name="plus" /> Add color
    </a>
@endsection

@section('content')
    <section class="panel table-panel">
        <form class="table-toolbar" method="GET" action="{{ route('admin.colors.index') }}">
            <label class="search-input" for="color-search">
                <x-sss-admin.icon name="search" />
                <input id="color-search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search colors or codes" aria-label="Search colors or codes">
            </label>

            <select class="form-select" name="status" aria-label="Filter color visibility">
                <option value="">All visibility</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button class="btn btn-light" type="submit">Filter</button>

            @if ($search !== '' || $status !== null)
                <a class="text-link" href="{{ route('admin.colors.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Code</th>
                        <th>Variants</th>
                        <th>Visibility</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($colors as $color)
                        @php
                            $colorCode = is_string($color->code) && preg_match('/^#[0-9A-F]{6}$/i', $color->code)
                                ? strtoupper($color->code)
                                : null;
                        @endphp
                        <tr>
                            <td><strong>{{ $color->name }}</strong></td>
                            <td>
                                @if ($colorCode)
                                    <span aria-hidden="true"
                                        style="display:inline-block;width:1rem;height:1rem;margin-right:.4rem;vertical-align:-.15rem;border:1px solid rgba(0,0,0,.2);background-color:{{ $colorCode }};"></span>
                                    <code>{{ $colorCode }}</code>
                                @elseif ($color->code)
                                    {{ $color->code }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td>
                                {{ $color->variants_count }}
                                @if ($color->variants_count > 0)
                                    <small>In use by product variants</small>
                                @endif
                            </td>
                            <td>
                                <span class="status {{ $color->is_active ? 'status-active' : 'status-draft' }}">
                                    {{ $color->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="page-actions">
                                    <a class="text-link" href="{{ route('admin.colors.edit', $color) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.colors.destroy', $color) }}"
                                        onsubmit="return confirm('Delete this unused color? This cannot be undone.')">
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
                                {{ $search !== '' || $status !== null ? 'No colors match these filters.' : 'No colors yet. Add the shades you plan to offer in product variants.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($colors->total() > 0)
            <div class="table-foot">
                <span>
                    Showing {{ $colors->firstItem() }}&ndash;{{ $colors->lastItem() }} of {{ $colors->total() }} colors
                </span>
                {{ $colors->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
