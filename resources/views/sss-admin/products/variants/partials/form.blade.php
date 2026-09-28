<form method="POST" action="{{ $formAction }}">
    @csrf

    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>{{ $formHeading }}</h2>
                <p>{{ $formDescription }}</p>
            </div>
        </div>

        @if ($isCreating)
            <div class="two-col">
                <div class="field">
                    <label for="size_id">Size</label>
                    <select class="form-select @error('size_id') is-invalid @enderror" id="size_id" name="size_id"
                        @error('size_id') aria-invalid="true" aria-describedby="size-id-error" @enderror>
                        <option value="">No size</option>
                        @foreach ($sizes as $size)
                            <option value="{{ $size->getKey() }}" @selected((string) old('size_id') === (string) $size->getKey())>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                    <small>Active sizes only.</small>
                    @error('size_id')
                        <div class="invalid-feedback d-block" id="size-id-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="color_id">Color</label>
                    <select class="form-select @error('color_id') is-invalid @enderror" id="color_id" name="color_id"
                        @error('color_id') aria-invalid="true" aria-describedby="color-id-error" @enderror>
                        <option value="">No color</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color->getKey() }}" @selected((string) old('color_id') === (string) $color->getKey())>
                                {{ $color->name }}{{ $color->code ? ' ('.$color->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small>Active colors only. Select a size, a color, or both.</small>
                    @error('color_id')
                        <div class="invalid-feedback d-block" id="color-id-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if ($sizes->isEmpty() && $colors->isEmpty())
                <p class="small muted">Create and activate a Size or Color before adding an option variant.</p>
            @endif
        @endif

        <div class="field">
            <label for="sku">Variant SKU <span>*</span></label>
            <input class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" type="text"
                value="{{ old('sku', $variant?->sku) }}" maxlength="255" required
                placeholder="e.g. SSS-SH-NVY-S"
                @error('sku') aria-invalid="true" aria-describedby="sku-error" @enderror>
            @error('sku')
                <div class="invalid-feedback d-block" id="sku-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="two-col">
            <div class="field">
                <label for="price">Base price override (INR)</label>
                <input class="form-control @error('price') is-invalid @enderror" id="price" name="price" type="number"
                    min="0.01" max="99999999.99" step="0.01" value="{{ old('price', $variant?->price) }}"
                    @error('price') aria-invalid="true" aria-describedby="price-error" @enderror>
                <small>Leave blank to inherit the product base price of ₹{{ number_format((float) $product->price, 2) }}.</small>
                @error('price')
                    <div class="invalid-feedback d-block" id="price-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="sale_price">Sale price override (INR)</label>
                <input class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" type="number"
                    min="0.01" max="99999999.99" step="0.01" value="{{ old('sale_price', $variant?->sale_price) }}"
                    @error('sale_price') aria-invalid="true" aria-describedby="sale-price-error" @enderror>
                <small>Optional. It must be lower than this variant's effective base price.</small>
                @error('sale_price')
                    <div class="invalid-feedback d-block" id="sale-price-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="two-col">
            <div class="field">
                <label for="stock">Stock quantity <span>*</span></label>
                <input class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" type="number"
                    min="0" max="4294967295" step="1" value="{{ old('stock', $variant?->stock ?? 0) }}" required
                    @error('stock') aria-invalid="true" aria-describedby="stock-error" @enderror>
                @error('stock')
                    <div class="invalid-feedback d-block" id="stock-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="low_stock_limit">Low-stock limit <span>*</span></label>
                <input class="form-control @error('low_stock_limit') is-invalid @enderror" id="low_stock_limit"
                    name="low_stock_limit" type="number" min="0" max="4294967295" step="1"
                    value="{{ old('low_stock_limit', $variant?->low_stock_limit ?? 5) }}" required
                    @error('low_stock_limit') aria-invalid="true" aria-describedby="low-stock-limit-error" @enderror>
                @error('low_stock_limit')
                    <div class="invalid-feedback d-block" id="low-stock-limit-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if ($isCreating && $requiresTransition)
            <section class="note-panel compact">
                <span class="eyebrow">DEFAULT STOCK TRANSITION REQUIRED</span>
                <p>Default SKU <strong>{{ $defaultVariant->sku }}</strong> currently has <strong>{{ $defaultVariant->stock }}</strong> units. Choose one explicit transition before adding this first option variant.</p>

                <label class="check" for="transition-transfer-all">
                    <input id="transition-transfer-all" name="transition_action" type="radio" value="transfer_all"
                        @checked(old('transition_action') === 'transfer_all')>
                    Transfer all {{ $defaultVariant->stock }} units to this new variant and deactivate the default variant
                </label>
                <small>When selected, this new variant's stock must be exactly {{ $defaultVariant->stock }}.</small>

                <label class="check" for="transition-deactivate-empty">
                    <input id="transition-deactivate-empty" name="transition_action" type="radio" value="deactivate_zero_stock"
                        @checked(old('transition_action') === 'deactivate_zero_stock')>
                    The default variant already has zero stock; deactivate it without transfer
                </label>

                @error('transition_action')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </section>
        @endif

        <input name="is_active" type="hidden" value="0">
        <label class="check" for="is_active">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                @checked(old('is_active', $variant?->is_active ?? true))>
            Make this variant active
        </label>
        @error('is_active')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <button class="btn btn-dark w-100" type="submit">{{ $submitLabel }}</button>
    </section>
</form>
