@extends('layouts.store')

@section('title', 'Your saved pieces — sss')
@section('page-key', 'wishlist')

@section('content')
    <div class="container-wide section" data-server-wishlist>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / Your saved pieces</div>
            <h1>Your saved pieces</h1>
            <p>A little collection of things you love.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="empty">
                <h2>A little space for your favourites.</h2>
                <p>Save pieces from the collection to keep them here.</p>
                <a class="btn btn-dark" href="{{ route('store.shop') }}">Explore the collection</a>
            </div>
        @else
            <p class="small muted mb-4">{{ $items->total() }} {{ IlluminateSupportStr::plural('saved piece', $items->total()) }}</p>

            <div class="product-grid shop-grid">
                @foreach ($items as $item)
                    @if ($item->product)
                        @php($product = $item->product)
                        <article class="product-card">
                            <div class="product-photo">
                                <a href="{{ route('store.products.show', $product) }}" aria-label="View {{ $product->name }}">
                                    <img src="{{ $product->wishlist_image_url }}" alt="{{ $product->name }}" loading="lazy">
                                </a>
                                @unless ($product->wishlist_is_available)
                                    <span class="tag">Out of stock</span>
                                @endunless
                            </div>

                            <div class="product-meta">
                                <div>
                                    <h3><a href="{{ route('store.products.show', $product) }}">{{ $product->name }}</a></h3>
                                </div>
                            </div>

                            <span>
                                @if ($product->wishlist_shows_from_price)
                                    From
                                @endif
                                ₹{{ number_format((float) $product->wishlist_effective_price, 2) }}
                            </span>

                            @unless ($product->wishlist_is_available)
                                <p class="small muted mt-2 mb-0">This product is currently out of stock. It will remain saved here.</p>
                            @endunless

                            <form method="POST" action="{{ route('store.wishlist.destroy', $item) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="text-button" type="submit">Remove saved piece</button>
                            </form>
                        </article>
                    @else
                        <article class="product-card">
                            <div class="product-photo">
                                <img src="{{ asset('sss-admin/images/product-placeholder.svg') }}" alt="Unavailable saved product" loading="lazy">
                                <span class="tag">Unavailable</span>
                            </div>

                            <div class="product-meta">
                                <div>
                                    <h3>Saved product unavailable</h3>
                                    <p class="small muted mb-0">This product is no longer publicly available.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('store.wishlist.destroy', $item) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="text-button" type="submit">Remove saved piece</button>
                            </form>
                        </article>
                    @endif
                @endforeach
            </div>

            @if ($items->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $items->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </div>
@endsection
