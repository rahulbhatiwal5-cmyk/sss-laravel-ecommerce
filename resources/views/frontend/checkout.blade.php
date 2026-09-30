@extends('layouts.store')

@section('title', 'Checkout review — sss')
@section('page-key', 'checkout')

@section('content')
    <div class="container-wide section" data-server-checkout>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / <a href="{{ route('store.cart') }}">Bag</a> / Checkout review</div>
            <h1>Checkout review</h1>
            <p>Review your selected pieces and delivery address before order placement is added.</p>
        </div>

        <div class="checkout-progress" aria-label="Checkout progress">
            01 Bag <span>—</span> <strong>02 Review</strong> <span>—</span> 03 Order placement
        </div>

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if (! $isCheckoutReady)
            <div class="alert alert-danger" role="alert">
                <strong>Your bag needs attention before checkout can continue.</strong>
                <p class="mb-0 mt-1">Fix or remove every unavailable line and reduce any quantity that exceeds current stock.</p>
            </div>
        @endif

        <div class="checkout-layout">
            <section aria-label="Checkout review details">
                <div class="panel">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h2 class="mb-1">Your bag</h2>
                            <p class="small muted mb-0">{{ $itemCount }} {{ IlluminateSupportStr::plural('piece', $itemCount) }} selected</p>
                        </div>
                        <a class="text-link" href="{{ route('store.cart') }}">Edit bag ←</a>
                    </div>

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
                                <p class="small mb-2">Quantity: <strong>{{ $line['quantity'] }}</strong></p>
                                <p class="small mb-0">
                                    Current unit price: <strong>{{ $line['unit_price_display'] }}</strong>
                                    @if ($line['base_price_display'])
                                        <del>{{ $line['base_price_display'] }}</del>
                                    @endif
                                </p>

                                @if ($line['status'] !== 'Available')
                                    <span class="status-pill mt-3">{{ $line['status'] }}</span>
                                    <p class="small muted mt-2 mb-0">{{ $line['status_message'] }}</p>
                                @endif
                            </div>

                            <div>
                                <span class="micro muted">Line total</span>
                                <strong class="d-block">{{ $line['line_total_display'] }}</strong>
                                @unless ($line['included_in_subtotal'])
                                    <p class="small muted mt-2 mb-0">Fix this line in your bag before ordering.</p>
                                @endunless
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($addresses->isEmpty())
                    <section class="panel mt-4" aria-labelledby="delivery-address-heading">
                        <h2 id="delivery-address-heading">Delivery address</h2>
                        <p class="muted">Add a saved delivery address before order placement is available.</p>
                        <a class="btn btn-dark" href="{{ route('store.addresses.create') }}">Add address ↗</a>
                    </section>
                @else
                    <section class="panel mt-4" aria-labelledby="delivery-address-heading">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <h2 id="delivery-address-heading" class="mb-0">Delivery address</h2>
                            <a class="text-link" href="{{ route('store.addresses') }}">Manage addresses ↗</a>
                        </div>

                        <form method="GET" action="{{ route('store.checkout') }}">
                            <fieldset class="border-0 p-0 m-0">
                                <legend class="visually-hidden">Choose delivery address</legend>
                                @foreach ($addresses as $address)
                                    <label class="payment-option d-block mb-3">
                                        <input type="radio" name="address" value="{{ $address->getKey() }}"
                                            @checked($selectedAddress?->is($address))>
                                        <span>
                                            <strong>{{ $address->name }}</strong>
                                            @if ($address->is_default)
                                                <span class="muted small"> · Default</span>
                                            @endif
                                            <br>
                                            {{ $address->address_line_1 }}, {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                                        </span>
                                    </label>
                                @endforeach
                            </fieldset>
                            <button class="btn btn-outline-dark" type="submit">Use selected address</button>
                        </form>

                        @if ($selectedAddress)
                            <div class="mt-4 pt-3 border-top">
                                <span class="eyebrow">SELECTED DELIVERY ADDRESS</span>
                                <h3 class="mt-2">{{ $selectedAddress->name }}</h3>
                                <p class="mb-2">
                                    {{ $selectedAddress->address_line_1 }}<br>
                                    @if ($selectedAddress->address_line_2)
                                        {{ $selectedAddress->address_line_2 }}<br>
                                    @endif
                                    @if ($selectedAddress->landmark)
                                        {{ $selectedAddress->landmark }}<br>
                                    @endif
                                    {{ $selectedAddress->city }}, {{ $selectedAddress->state }} {{ $selectedAddress->postal_code }}<br>
                                    {{ $selectedAddress->country }}<br>
                                    {{ $selectedAddress->phone }}
                                </p>
                                <a class="text-link" href="{{ route('store.addresses.edit', $selectedAddress) }}">Edit this address ↗</a>
                            </div>
                        @endif
                    </section>
                @endif
            </section>

            <aside>
                <div class="summary-panel">
                    <h2>Review summary</h2>
                    <div class="summary-line total">
                        <strong>Merchandise subtotal</strong>
                        @if ($isCheckoutReady)
                            <strong>{{ $subtotalDisplay }}</strong>
                        @else
                            <strong>Pending bag fixes</strong>
                        @endif
                    </div>

                    @if ($problemLineCount > 0)
                        <p class="small muted">{{ $problemLineCount }} {{ IlluminateSupportStr::plural('line', $problemLineCount) }} must be fixed in your bag before this review can continue.</p>
                    @endif

                    <p class="small muted">Shipping and tax policies are not configured. This merchandise subtotal is not a final payable total.</p>
                    <a class="text-link" href="{{ route('store.cart') }}">← Return to bag</a>
                </div>

                <div class="panel mt-4">
                    <h2>Order placement comes next</h2>
                    <p class="muted mb-0">This page does not create an order, collect payment, reserve stock, or clear your bag. Prices and stock will be revalidated again when order placement is implemented.</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
