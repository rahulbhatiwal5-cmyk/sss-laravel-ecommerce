@extends('layouts.store')

@section('title', 'The everyday collection — sss')
@section('page-key', 'shop')

@section('content')
    <div class="container-wide section" data-server-shop>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / The everyday collection</div>
            <h1>The everyday collection</h1>
            <p>Timeless shapes. Fresh details. Pieces you’ll reach for again and again.</p>
        </div>

        <form method="GET" action="{{ route('store.shop') }}">
            <div class="search-bar">
                <label class="visually-hidden" for="shop-search">Search products</label>
                <input class="form-control" id="shop-search" name="q" type="search" value="{{ $search }}"
                    placeholder="Search products" autocomplete="off">
                <button class="btn btn-dark" type="submit">Search</button>
            </div>

            <div class="shop-layout">
                <aside class="filters">
                    <div class="filter-head">
                        <h3>Filters</h3>
                        @if ($hasFilters)
                            <a class="text-button" href="{{ route('store.shop') }}">Reset</a>
                        @endif
                    </div>

                    <label for="shop-category">Collection</label>
                    <select class="form-select" id="shop-category" name="category">
                        <option value="">All collections</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}"
                                @selected((string) $selectedCategoryId === (string) $category->getKey())>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="shop-brand">Brand</label>
                    <select class="form-select" id="shop-brand" name="brand">
                        <option value="">All brands</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->slug }}"
                                @selected((string) $selectedBrandId === (string) $brand->getKey())>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="shop-sort">Sort by</label>
                    <select class="form-select" id="shop-sort" name="sort">
                        <option value="newest">Newest</option>
                    </select>

                    <button class="btn btn-outline-dark w-100 mt-4" type="submit">Apply filters</button>
                    <p class="small muted mt-4">Showing published products from active collections.</p>
                    <a href="{{ url('/size-guide') }}" class="text-link">Find your fit ↗</a>
                </aside>

                <div>
                    <div class="shop-toolbar">
                        <span aria-live="polite">
                            {{ $products->total() }} {{ \Illuminate\Support\Str::plural('piece', $products->total()) }}
                        </span>
                        <span class="small muted">Newest first</span>
                    </div>

                    <div class="product-grid shop-grid">
                        @forelse ($products as $product)
                            <article class="product-card">
                                <div class="product-photo">
                                    <a href="{{ route('store.products.show', $product) }}" aria-label="View {{ $product->name }}">
                                        <img src="{{ $product->shop_image_url }}" alt="{{ $product->name }}" loading="lazy">
                                    </a>
                                    @unless ($product->shop_is_available)
                                        <span class="tag">Out of stock</span>
                                    @endunless
                                </div>

                                <div class="product-meta">
                                    <div>
                                        <span class="micro muted">
                                            {{ $product->category?->name }}@if ($product->brand) / {{ $product->brand->name }}@endif
                                        </span>
                                        <h3><a href="{{ route('store.products.show', $product) }}">{{ $product->name }}</a></h3>
                                    </div>
                                </div>

                                <span>
                                    @if ($product->shop_shows_from_price)
                                        From
                                    @endif
                                    ₹{{ number_format((float) $product->shop_effective_price, 2) }}
                                </span>
                            </article>
                        @empty
                            <div class="empty">
                                @if ($hasFilters)
                                    <h2>No pieces found.</h2>
                                    <p>Try a different search or clear the filters to see the full collection.</p>
                                    <a class="btn btn-dark" href="{{ route('store.shop') }}">Clear filters</a>
                                @else
                                    <h2>The collection is coming soon.</h2>
                                    <p>Published products from active collections will appear here.</p>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    @if ($products->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
