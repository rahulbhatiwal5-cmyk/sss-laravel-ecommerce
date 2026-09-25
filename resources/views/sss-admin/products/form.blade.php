@extends('layouts.sss-admin')
@section('title', isset($product) ? 'Edit product' : 'Add product')
@section('subtitle', 'Bring another everyday favourite into your collection.')
@section('actions')<a class="btn btn-light" href="{{ route('admin.products.index') }}">
    ← All products</a>@endsection
@section('content')
    <form onsubmit="return false" data-preview-form="Product details validated. This layout preview does not save products."
        id="product-form">
        <div class="form-layout">
            <div>
                <section class="panel">
                    <div class="panel-heading">
                        <div>
                            <h2>The essentials</h2>
                            <p>A clear name and thoughtful details make all the difference.</p>
                        </div>
                    </div>
                    <div class="field"><label for="name">Product name <span>*</span></label><input class="form-control"
                            name="name" id="name" value="{{ old('name', $product['name'] ?? '') }}"
                            placeholder="e.g. The Everyday Shirt" required maxlength="150"></div>
                    <div class="two-col">
                        <div class="field"><label for="sku">SKU <span>*</span></label><input class="form-control"
                                name="sku" id="sku" value="{{ old('sku', $product['sku'] ?? '') }}"
                                placeholder="SSS-SH-001" required></div>
                        <div class="field"><label for="collection">Collection <span>*</span></label><select id="collection"
                                name="collection" class="form-select" required>
                                <option value="">Choose a collection</option>
                                @foreach (['Women', 'Men', 'Kids', 'Accessories'] as $collection)
                                    <option @selected(old('collection', $product['category'] ?? '') === $collection)>{{ $collection }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="field"><label for="description">Description</label>
                        <textarea class="form-control" rows="5" id="description" name="description"
                            placeholder="Describe the fit, fabric, and details that make this piece special.">{{ old('description', '') }}</textarea><small>Keep product descriptions useful, accurate, and easy to
                            read.</small>
                    </div>
                </section>
                <section class="panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Product photography</h2>
                            <p>Give every detail a moment.</p>
                        </div>
                    </div><label class="upload-box" for="product-image"><span class="upload-symbol">↥</span><strong>Choose a
                            product image</strong><span>JPEG, PNG, or WebP · up to 5 MB</span><input type="file"
                            id="product-image" name="image" accept="image/jpeg,image/png,image/webp"></label>
                    <div id="image-preview" class="image-preview" hidden><img id="preview-image"
                            alt="Selected product preview"><button type="button" id="clear-image" class="text-link">Remove
                            selected image</button></div>
                    @isset($product)
                        <p class="small muted mt-3">Current sample illustration</p><img class="current-product-image"
                            src="{{ asset('sss-admin/images/product-' . $product['image'] . '.svg') }}"
                            alt="{{ $product['name'] }}">
                    @endisset
                </section>
                <section class="panel">
                    <h2>Pricing & inventory</h2>
                    <div class="two-col">
                        <div class="field"><label for="price">Selling price (₹) <span>*</span></label><input
                                class="form-control" type="number" name="price" id="price" min="0"
                                step="0.01" value="{{ old('price', $product['price'] ?? '') }}" required></div>
                        <div class="field"><label for="compare-price">Compare-at price (₹)</label><input
                                class="form-control" type="number" name="compare_price" id="compare-price" min="0"
                                step="0.01"></div>
                    </div>
                    <div class="field"><label for="stock">Stock quantity <span>*</span></label><input
                            class="form-control" name="stock" id="stock" type="number" min="0" step="1"
                            value="{{ old('stock', $product['stock'] ?? 0) }}" required></div>
                    <p class="small muted">Layout example uses total stock. Track size/colour variant stock in your backend.
                    </p>
                </section>
            </div>
            <aside>
                <section class="panel">
                    <h2>Visibility</h2>
                    <div class="field"><label for="status">Product status</label><select class="form-select"
                            name="status" id="status">
                            @foreach (['Draft', 'Active'] as $status)
                                <option @selected(old('status', $product['status'] ?? 'Draft') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="small muted">Active products are intended for the storefront once connected to your backend.
                    </p>
                </section>
                <section class="panel">
                    <h2>Available sizes</h2>
                    <div class="size-grid">
                        @foreach (['XS', 'S', 'M', 'L', 'XL', 'One size'] as $size)
                            <label><input type="checkbox" name="sizes[]"
                                    value="{{ $size }}"><span>{{ $size }}</span></label>
                        @endforeach
                    </div>
                </section>
                <section class="note-panel compact"><span class="eyebrow">BEFORE YOU SAVE</span>
                    <p>Add the right photos, check pricing, and choose the sizes your customers can order.</p><button
                        class="btn btn-dark w-100" type="submit">Preview save <x-sss-admin.icon
                            name="arrow" /></button>
                    <p class="small muted mt-3">Layout only. Changes are not stored.</p>
                </section>
            </aside>
        </div>
    </form>
@endsection
