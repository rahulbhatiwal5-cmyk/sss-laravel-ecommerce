@extends('layouts.sss-admin')

@section('title', 'Products')
@section('subtitle', 'Your live catalogue, organised in one place.')
@section('topbar-label', 'Live product data')

@section('actions')
    <a class="btn btn-dark" href="{{ route('admin.products.create') }}">
        Add product <x-sss-admin.icon name="plus" />
    </a>
@endsection

@section('content')
    <section class="panel table-panel">
        <form class="table-toolbar" method="GET" action="{{ route('admin.products.index') }}">
            <label class="search-input" for="product-search">
                <x-sss-admin.icon name="search" />
                <input id="product-search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search name or slug" aria-label="Search products">
            </label>

            <select class="form-select" name="category_id" aria-label="Filter products by category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->getKey() }}"
                        @selected((string) $categoryId === (string) $category->getKey())>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select class="form-select" name="status" aria-label="Filter product status">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button class="btn btn-light" type="submit">Filter</button>

            @if ($search !== '' || $categoryId !== null || $status !== null)
                <a class="text-link" href="{{ route('admin.products.index') }}">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Variants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <img src="{{ $product->listing_image_url ?: asset('sss-admin/images/product-placeholder.svg') }}"
                                        alt="{{ $product->name }}" loading="lazy">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <small>{{ $product->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td>
                                <strong>₹{{ number_format((float) $product->price, 2) }}</strong>
                                @if ($product->sale_price !== null)
                                    <small>Sale: ₹{{ number_format((float) $product->sale_price, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="status {{ $product->is_active ? 'status-active' : 'status-draft' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $product->variants_count }}</td>
                            <td>
                                <a class="text-link" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">
                                {{ $search !== '' || $categoryId !== null || $status !== null
                                    ? 'No products match these filters.'
                                    : 'No products yet. Products you add later will appear here.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->total() > 0)
            <div class="table-foot">
                <span>
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
                </span>
                {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
