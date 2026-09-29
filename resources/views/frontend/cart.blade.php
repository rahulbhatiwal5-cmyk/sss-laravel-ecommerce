@extends('layouts.store')

@section('title', 'Your shopping bag — sss')
@section('page-key', 'cart')

@section('content')
    <div class="container-wide section" data-server-cart>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / Your shopping bag</div>
            <h1>Your shopping bag</h1>
            <p>Review your selected pieces before checkout.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="mb-2">Please correct the following before continuing:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="empty">
                <h2>Your bag is waiting.</h2>
                <p>Start with something you’ll love wearing.</p>
                <a class="btn btn-dark" href="{{ route('store.shop') }}">Explore the collection</a>
            </div>
        @else
            <div class="cart-layout">
                <section aria-label="Bag items">
                    <p class="small muted">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('piece', $itemCount) }} selected</p>

                    @foreach ($items as $line)
                        <article class="cart-row">
                            @if ($line['product_url'])
                                <a href="{{ $line['product_url'] }}">
                                    <img src="{{ $line['image_url'] }}" alt="{{ $line['name'] }}">
                                </a>
                            @else
                                <img src="{{ $line['image_url'] }}" alt="{{ $line['name'] }}">
                            @endif

                            <div>
                                @if ($line['product_url'])
                                    <h3><a href="{{ $line['product_url'] }}">{{ $line['name'] }}</a></h3>
                                @else
                                    <h3>{{ $line['name'] }}</h3>
                                @endif

                                <p class="small muted mb-2">{{ $line['option_label'] }} · SKU: {{ $line['sku'] }}</p>
                                <p class="small mb-2">
                                    Unit price: <strong>{{ $line['unit_price_display'] }}</strong>
                                    @if ($line['base_price_display'])
                                        <del>{{ $line['base_price_display'] }}</del>
                                    @endif
                                </p>

                                @if ($line['status'] !== 'Available')
                                    <span class="status-pill">{{ $line['status'] }}</span>
                                    <p class="small muted mt-2">{{ $line['status_message'] }}</p>
                                @endif

                                @if ($line['can_update'])
                                    <form action="{{ route('store.cart.update', ['cartItem' => $line['id']]) }}" method="POST"
                                        class="d-flex align-items-end gap-2 mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="small d-block mb-1" for="cart-quantity-{{ $line['id'] }}">Quantity</label>
                                            <input class="form-control" id="cart-quantity-{{ $line['id'] }}" name="quantity" type="number"
                                                min="1" max="{{ $line['quantity_max'] }}" step="1" value="{{ $line['quantity'] }}" required>
                                        </div>
                                        <button class="btn btn-outline-dark" type="submit">Update</button>
                                    </form>
                                @endif

                                <form action="{{ route('store.cart.destroy', ['cartItem' => $line['id']]) }}" method="POST" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-button" type="submit">Remove</button>
                                </form>
                            </div>

                            <div>
                                <span class="micro muted">Line total</span>
                                <strong class="d-block">{{ $line['line_total_display'] }}</strong>
                                @unless ($line['included_in_subtotal'])
                                    <p class="small muted mt-2 mb-0">Not included in subtotal.</p>
                                @endunless
                            </div>
                        </article>
                    @endforeach

                    <a href="{{ route('store.shop') }}" class="text-link mt-4">← Continue shopping</a>
                </section>

                <aside>
                    <div class="summary-panel">
                        <h2>Bag summary</h2>
                        <div class="summary-line total">
                            <strong>Subtotal</strong>
                            <strong>{{ $subtotalDisplay }}</strong>
                        </div>

                        @if ($problemLineCount > 0)
                            <p class="small muted">{{ $problemLineCount }} {{ \Illuminate\Support\Str::plural('line', $problemLineCount) }} cannot currently be purchased and {{ $problemLineCount === 1 ? 'is' : 'are' }} excluded from the subtotal.</p>
                        @endif

                        <p class="small muted">Shipping, tax, coupons, and checkout will be added later. Stock is checked again at checkout.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
