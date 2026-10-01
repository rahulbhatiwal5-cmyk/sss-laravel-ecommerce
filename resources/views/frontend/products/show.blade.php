@extends('layouts.store')

@section('title', $product->name . ' — sss')
@section('page-key', 'product-detail')

@section('content')
    @php
        $oldVariantId = old('variant_id');
        $selectedVariantId = is_scalar($oldVariantId) ? (string) $oldVariantId : (string) ($initialVariant['id'] ?? '');
        $oldQuantity = old('quantity');
        $selectedQuantity = is_scalar($oldQuantity) ? (string) $oldQuantity : '1';
    @endphp

    <div class="container-wide section" data-server-product-detail
        data-initial-variant-id="{{ $selectedVariantId }}">
        <div class="crumb">
            <a href="{{ route('store.home') }}">Home</a> /
            <a href="{{ route('store.shop') }}">Shop</a> /
            <span>{{ $product->name }}</span>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <article class="product-detail">
            <div>
                <img class="product-main-image" src="{{ $mainImageUrl }}" alt="{{ $product->name }}">

                @if ($galleryImages->isNotEmpty())
                    <div class="row row-cols-4 g-2 mt-2" aria-label="{{ $product->name }} gallery">
                        @foreach ($galleryImages as $galleryImage)
                            <div class="col">
                                <img class="w-100 border" src="{{ $galleryImage['url'] }}" alt="{{ $galleryImage['alt'] }}"
                                    style="aspect-ratio: 3 / 4; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-info">
                <span class="eyebrow">
                    <a href="{{ route('store.shop', ['category' => $product->category?->slug]) }}">
                        {{ $product->category?->name }}
                    </a>
                    @if ($product->brand)
                        / <a href="{{ route('store.shop', ['brand' => $product->brand->slug]) }}">{{ $product->brand->name }}</a>
                    @endif
                </span>

                <h1>{{ $product->name }}</h1>

                @if ($product->short_description)
                    <p class="muted">{{ $product->short_description }}</p>
                @endif

                <div class="price" aria-live="polite">
                    <span class="micro muted" data-detail-sale-label @if (($initialVariant['sale_price_display'] ?? null) === null) hidden @endif>
                        Sale price
                    </span>
                    <span data-detail-price-prefix @unless ($summaryShowsFromPrice) hidden @endunless>From </span>
                    <strong data-detail-effective-price>{{ $initialVariant['effective_price_display'] ?? $summaryPriceDisplay }}</strong>
                    <del data-detail-base-price @if (($initialVariant['sale_price_display'] ?? null) === null) hidden @endif>
                        {{ $initialVariant['base_price_display'] ?? '' }}
                    </del>
                </div>

                <p class="micro muted mb-2">SKU: <span data-detail-sku>{{ $initialVariant['sku'] ?? '—' }}</span></p>
                <p class="mb-4">
                    <span class="status-pill" data-detail-availability>
                        @if ($initialVariant)
                            {{ $initialVariant['is_available'] ? 'In stock' : 'Out of stock' }}
                        @elseif ($hasOptions)
                            Choose an option
                        @else
                            Unavailable
                        @endif
                    </span>
                </p>

                @if ($hasOptions)
                    <fieldset class="border-0 p-0 m-0 mb-4">
                        <legend class="eyebrow">Choose size / colour</legend>
                        <p class="small muted">Each choice below is an actual product combination. Out-of-stock choices remain visible.</p>
                        <div class="d-flex flex-wrap gap-2" data-variant-options>
                            @foreach ($variantData as $variant)
                                <label class="btn btn-outline-dark mb-0" style="gap: 8px;">
                                    <input class="form-check-input m-0" type="radio" name="variant" value="{{ $variant['id'] }}"
                                        data-variant-option>
                                    <span>{{ $variant['label'] }}</span>
                                    @unless ($variant['is_available'])
                                        <small>Out of stock</small>
                                    @endunless
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <p class="muted" data-detail-selection-prompt @if ($initialVariant) hidden @endif>
                    @if ($hasOptions)
                        Choose a size or colour option to see its exact price, SKU, and availability.
                    @else
                        This product does not currently have an active option available.
                    @endif
                </p>

                @if ($variantData->isNotEmpty())
                    <form method="POST" action="{{ route('store.cart.store', $product) }}" data-add-to-cart-form>
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $selectedVariantId }}"
                            data-cart-variant-input>

                        <div class="add-row">
                            <div style="width: 100px;">
                                <label class="visually-hidden" for="product-quantity">Quantity</label>
                                <input class="form-control" id="product-quantity" name="quantity" type="number" min="1" step="1"
                                    value="{{ $selectedQuantity }}" data-cart-quantity required
                                    @if ($initialVariant) max="{{ $initialVariant['stock'] }}" @endif>
                            </div>
                            <button class="btn btn-dark" type="submit" data-add-to-cart-button
                                @if (! $initialVariant || ! $initialVariant['is_available']) disabled @endif>
                                Add to bag
                            </button>
                        </div>

                        @error('variant_id')
                            <p class="small text-danger mb-2">{{ $message }}</p>
                        @enderror
                        @error('quantity')
                            <p class="small text-danger mb-2">{{ $message }}</p>
                        @enderror
                        @if (session('error'))
                            <p class="small text-danger mb-2">{{ session('error') }}</p>
                        @endif
                    </form>
                @endif

                @if ($canManageWishlist)
                    @if ($wishlistItemId)
                        <form method="POST" action="{{ route('store.wishlist.destroy', $wishlistItemId) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-dark w-100" type="submit">Remove from wishlist</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('store.wishlist.store', $product) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-outline-dark w-100" type="submit">Save to wishlist</button>
                        </form>
                    @endif
                @elseif ($isWishlistGuest)
                    <a class="btn btn-outline-dark w-100 mt-3" href="{{ route('store.login') }}">Sign in to save</a>
                @endif

                @if ($product->description)
                    <details open>
                        <summary>Description</summary>
                        <p style="white-space: pre-line;">{{ $product->description }}</p>
                    </details>
                @endif

                @if ($product->material)
                    <details>
                        <summary>Material</summary>
                        <p style="white-space: pre-line;">{{ $product->material }}</p>
                    </details>
                @endif

                @if ($product->care_instructions)
                    <details>
                        <summary>Care instructions</summary>
                        <p style="white-space: pre-line;">{{ $product->care_instructions }}</p>
                    </details>
                @endif
            </div>
        </article>

        <script type="application/json" data-variant-payload>@json($variantData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-server-product-detail]');

            if (!root) {
                return;
            }

            const payload = root.querySelector('[data-variant-payload]');

            if (!payload) {
                return;
            }

            let variants;

            try {
                variants = JSON.parse(payload.textContent || '[]');
            } catch (error) {
                return;
            }

            if (!Array.isArray(variants)) {
                return;
            }

            const effectivePrice = root.querySelector('[data-detail-effective-price]');
            const basePrice = root.querySelector('[data-detail-base-price]');
            const saleLabel = root.querySelector('[data-detail-sale-label]');
            const pricePrefix = root.querySelector('[data-detail-price-prefix]');
            const sku = root.querySelector('[data-detail-sku]');
            const availability = root.querySelector('[data-detail-availability]');
            const selectionPrompt = root.querySelector('[data-detail-selection-prompt]');
            const cartVariantInput = root.querySelector('[data-cart-variant-input]');
            const addToCartButton = root.querySelector('[data-add-to-cart-button]');
            const cartQuantity = root.querySelector('[data-cart-quantity]');

            const selectVariant = (variantId) => {
                const variant = variants.find((candidate) => String(candidate.id) === String(variantId));

                if (!variant) {
                    return;
                }

                effectivePrice.textContent = variant.effective_price_display;
                basePrice.textContent = variant.base_price_display;
                basePrice.hidden = variant.sale_price_display === null;
                saleLabel.hidden = variant.sale_price_display === null;
                pricePrefix.hidden = true;
                sku.textContent = variant.sku || '—';
                availability.textContent = variant.is_available ? 'In stock' : 'Out of stock';

                if (cartVariantInput) {
                    cartVariantInput.value = variant.id;
                }

                if (addToCartButton) {
                    addToCartButton.disabled = !variant.is_available;
                }

                if (cartQuantity) {
                    cartQuantity.max = String(variant.stock);
                }

                root.querySelectorAll('[data-variant-option]').forEach((input) => {
                    input.checked = String(input.value) === String(variant.id);
                });

                if (selectionPrompt) {
                    selectionPrompt.hidden = true;
                }
            };

            root.querySelectorAll('[data-variant-option]').forEach((input) => {
                input.addEventListener('change', () => selectVariant(input.value));
            });

            if (root.dataset.initialVariantId !== '') {
                selectVariant(root.dataset.initialVariantId);
            }
        })();
    </script>
@endpush
