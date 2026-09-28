@extends('layouts.sss-admin')

@php
    $isEditing = $isEditing ?? false;
    $product = $product ?? null;
    $defaultVariant = $defaultVariant ?? null;
@endphp

@section('title', $isEditing ? 'Edit product' : 'Add product')
@section('subtitle', $isEditing ? 'Update product details, pricing, visibility, and photography. Manage inventory options separately.' : 'Add a simple product with its first inventory variant.')
@section('topbar-label', 'Live product data')

@section('actions')
    @if ($isEditing)
        <a class="btn btn-light" href="{{ route('admin.products.variants.index', $product) }}">Manage variants</a>
    @endif
    <a class="btn btn-light" href="{{ route('admin.products.index') }}">
        &larr; All products
    </a>
@endsection

@section('content')
    @if (! $isEditing && ! $canCreateProduct)
        <section class="panel">
            <span class="eyebrow">CATEGORY REQUIRED</span>
            <h2>Create a category first</h2>
            <p class="muted">Every product belongs to a category. Add one before creating your first product.</p>
            <a class="btn btn-dark" href="{{ route('admin.categories.create') }}">Create category</a>
        </section>
    @else
        @php
            $selectedCategoryId = (string) old('category_id', $product?->category_id ?? '');
            $selectedBrandId = (string) old('brand_id', $product?->brand_id ?? '');
        @endphp

        <form class="product-create-form" method="POST"
            action="{{ $isEditing ? route('admin.products.update', $product) : route('admin.products.store') }}"
            enctype="multipart/form-data"
            @if ($isEditing) data-product-edit-form @else data-product-create-form @endif>
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-layout">
                <div>
                    <section class="panel">
                        <div class="panel-heading">
                            <div>
                                <h2>The essentials</h2>
                                <p>A clear name, category, and useful details make this product easier to find.</p>
                            </div>
                        </div>

                        <div class="two-col">
                            <div class="field">
                                <label for="category_id">Category <span>*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                                    name="category_id" required
                                    @error('category_id') aria-invalid="true" aria-describedby="category-id-error" @enderror>
                                    <option value="">Choose a category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->getKey() }}"
                                            @selected($selectedCategoryId === (string) $category->getKey())>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block" id="category-id-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="brand_id">Brand</label>
                                <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id"
                                    @error('brand_id') aria-invalid="true" aria-describedby="brand-id-error" @enderror>
                                    <option value="">No brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->getKey() }}"
                                            @selected($selectedBrandId === (string) $brand->getKey())>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback d-block" id="brand-id-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="name">Product name <span>*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                                value="{{ old('name', $product?->name) }}" maxlength="255" required autofocus
                                placeholder="e.g. The Everyday Shirt"
                                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')
                                <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="slug">Slug</label>
                            <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" type="text"
                                value="{{ old('slug', $product?->slug) }}" maxlength="255" placeholder="everyday-shirt"
                                @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror>
                            <small>{{ $isEditing ? 'Leave blank to regenerate it from the product name.' : 'Leave blank to generate it from the product name.' }}</small>
                            @error('slug')
                                <div class="invalid-feedback d-block" id="slug-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="short_description">Short description</label>
                            <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description"
                                name="short_description" rows="3" placeholder="A quick summary for shoppers."
                                @error('short_description') aria-invalid="true" aria-describedby="short-description-error" @enderror>{{ old('short_description', $product?->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback d-block" id="short-description-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="5" placeholder="Describe the fit, fabric, and details that make this piece special."
                                @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $product?->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block" id="description-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="two-col">
                            <div class="field">
                                <label for="material">Material</label>
                                <textarea class="form-control @error('material') is-invalid @enderror" id="material" name="material"
                                    rows="3" placeholder="e.g. 100% organic cotton"
                                    @error('material') aria-invalid="true" aria-describedby="material-error" @enderror>{{ old('material', $product?->material) }}</textarea>
                                @error('material')
                                    <div class="invalid-feedback d-block" id="material-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="care_instructions">Care instructions</label>
                                <textarea class="form-control @error('care_instructions') is-invalid @enderror" id="care_instructions"
                                    name="care_instructions" rows="3" placeholder="e.g. Machine wash cold"
                                    @error('care_instructions') aria-invalid="true" aria-describedby="care-instructions-error" @enderror>{{ old('care_instructions', $product?->care_instructions) }}</textarea>
                                @error('care_instructions')
                                    <div class="invalid-feedback d-block" id="care-instructions-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="gender">Gender</label>
                            <input class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" type="text"
                                value="{{ old('gender', $product?->gender) }}" maxlength="255" placeholder="e.g. Unisex"
                                @error('gender') aria-invalid="true" aria-describedby="gender-error" @enderror>
                            @error('gender')
                                <div class="invalid-feedback d-block" id="gender-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>

                    @if ($isEditing)
                        @include('sss-admin.products.partials.media-previews', ['imagesEditable' => true])
                    @else
                        <section class="panel">
                            <div class="panel-heading">
                                <div>
                                    <h2>Product photography</h2>
                                    <p>Your main image is required. Add up to five supporting gallery images if needed.</p>
                                </div>
                            </div>

                            <div class="field">
                                <label class="upload-box" for="main_image">
                                    <span class="upload-symbol">Upload</span>
                                    <strong>Choose the main product image</strong>
                                    <span>JPEG, PNG, or WebP &middot; up to 2 MB</span>
                                    <input class="@error('main_image') is-invalid @enderror" id="main_image" name="main_image"
                                        type="file" accept="image/jpeg,image/png,image/webp" required data-main-image-input
                                        @error('main_image') aria-invalid="true" aria-describedby="main-image-error" @enderror>
                                </label>
                                <small>Choose the image shoppers should see first.</small>
                                @error('main_image')
                                    <div class="invalid-feedback d-block" id="main-image-error">{{ $message }}</div>
                                @enderror
                                <div class="image-preview" data-main-image-preview hidden>
                                    <img data-main-image-output alt="Selected main product image">
                                    <button class="text-link" type="button" data-clear-main-image>Remove selected image</button>
                                </div>
                            </div>

                            <div class="field">
                                <label class="upload-box" for="gallery">
                                    <span class="upload-symbol">Upload</span>
                                    <strong>Add gallery images</strong>
                                    <span>Optional &middot; up to 5 additional JPEG, PNG, or WebP images &middot; 2 MB each</span>
                                    <input class="@error('gallery') is-invalid @enderror" id="gallery" name="gallery[]" type="file"
                                        accept="image/jpeg,image/png,image/webp" multiple data-gallery-input
                                        aria-describedby="gallery-help"
                                        @error('gallery') aria-invalid="true" @enderror>
                                </label>
                                <small class="upload-help" id="gallery-help" data-gallery-count aria-live="polite">No gallery images selected.</small>
                                @error('gallery')
                                    <div class="invalid-feedback d-block" id="gallery-error">{{ $message }}</div>
                                @enderror
                                @foreach ($errors->get('gallery.*') as $galleryErrors)
                                    @foreach ($galleryErrors as $message)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @endforeach
                                @endforeach
                                <div class="gallery-preview-grid" data-gallery-preview role="list" hidden></div>
                                <div class="gallery-preview-actions" data-gallery-actions hidden>
                                    <button class="text-link" type="button" data-clear-gallery>Clear selected gallery</button>
                                </div>
                            </div>

                            @if ($errors->has('main_image') || $errors->has('gallery') || $errors->has('gallery.*'))
                                <p class="small muted">Please choose your images again before saving.</p>
                            @endif
                        </section>
                    @endif

                    <section class="panel">
                        <h2>Pricing</h2>
                        <div class="two-col">
                            <div class="field">
                                <label for="price">Selling price (INR) <span>*</span></label>
                                <input class="form-control @error('price') is-invalid @enderror" id="price" name="price"
                                    type="number" min="0.01" max="99999999.99" step="0.01"
                                    value="{{ old('price', $product?->price) }}" required
                                    @error('price') aria-invalid="true" aria-describedby="price-error" @enderror>
                                @error('price')
                                    <div class="invalid-feedback d-block" id="price-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="sale_price">Sale price (INR)</label>
                                <input class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price"
                                    type="number" min="0.01" max="99999999.99" step="0.01"
                                    value="{{ old('sale_price', $product?->sale_price) }}"
                                    @error('sale_price') aria-invalid="true" aria-describedby="sale-price-error" @enderror>
                                <small>Leave blank when there is no sale. It must be lower than the selling price.</small>
                                @error('sale_price')
                                    <div class="invalid-feedback d-block" id="sale-price-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    @if (! $isEditing)
                        <section class="panel">
                            <div class="panel-heading">
                                <div>
                                    <h2>Default inventory variant</h2>
                                    <p>This variant has no size or colour and inherits the product pricing.</p>
                                </div>
                            </div>

                            <div class="field">
                                <label for="variant_sku">Default variant SKU <span>*</span></label>
                                <input class="form-control @error('variant_sku') is-invalid @enderror" id="variant_sku"
                                    name="variant_sku" type="text" value="{{ old('variant_sku', $defaultVariant?->sku) }}" maxlength="255" required
                                    placeholder="e.g. SSS-SH-001"
                                    @error('variant_sku') aria-invalid="true" aria-describedby="variant-sku-error" @enderror>
                                <small>This SKU is used for inventory. Product SKU, variant prices, size, and colour cannot be changed here.</small>
                                @error('variant_sku')
                                    <div class="invalid-feedback d-block" id="variant-sku-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="two-col">
                                <div class="field">
                                    <label for="stock">Stock quantity <span>*</span></label>
                                    <input class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock"
                                        type="number" min="0" max="4294967295" step="1"
                                        value="{{ old('stock', $defaultVariant?->stock ?? 0) }}" required
                                        @error('stock') aria-invalid="true" aria-describedby="stock-error" @enderror>
                                    @error('stock')
                                        <div class="invalid-feedback d-block" id="stock-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label for="low_stock_limit">Low-stock limit <span>*</span></label>
                                    <input class="form-control @error('low_stock_limit') is-invalid @enderror" id="low_stock_limit"
                                        name="low_stock_limit" type="number" min="0" max="4294967295" step="1"
                                        value="{{ old('low_stock_limit', $defaultVariant?->low_stock_limit ?? 5) }}" required
                                        @error('low_stock_limit') aria-invalid="true" aria-describedby="low-stock-limit-error" @enderror>
                                    @error('low_stock_limit')
                                        <div class="invalid-feedback d-block" id="low-stock-limit-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>
                    @else
                        <section class="note-panel">
                            <span class="eyebrow">INVENTORY OPTIONS</span>
                            <h2>Manage size and color variants separately</h2>
                            <p>Variant SKUs, stock, low-stock limits, statuses, and price overrides are managed on the variant page.</p>
                            <a class="btn btn-light" href="{{ route('admin.products.variants.index', $product) }}">Manage variants</a>
                        </section>
                    @endif
                </div>

                <aside>
                    <section class="panel">
                        <h2>Visibility</h2>
                        <input name="is_active" type="hidden" value="0">
                        <label class="check" for="is_active">
                            <input id="is_active" name="is_active" type="checkbox" value="1"
                                @checked(old('is_active', $product?->is_active ?? true))>
                            Publish this product now
                        </label>
                        <p class="small muted">Inactive products remain in admin and are not published.</p>
                        @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </section>

                    <section class="panel">
                        <h2>Store placement</h2>
                        <input name="is_featured" type="hidden" value="0">
                        <label class="check" for="is_featured">
                            <input id="is_featured" name="is_featured" type="checkbox" value="1"
                                @checked(old('is_featured', $product?->is_featured ?? false))>
                            Mark as featured
                        </label>
                        <p class="small muted">Featured placement can be used by the storefront later.</p>
                        @error('is_featured')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </section>

                    <section class="note-panel compact">
                        <span class="eyebrow">{{ $isEditing ? 'READY TO UPDATE' : 'READY TO SAVE' }}</span>
                        <p>{{ $isEditing ? 'Check product details, pricing, visibility, and images before saving. Inventory is managed on the variant page.' : 'Check the pricing, inventory, and images before creating this product.' }}</p>
                        <button class="btn btn-dark w-100" type="submit">
                            {{ $isEditing ? 'Update product' : 'Create product' }} <x-sss-admin.icon name="arrow" />
                        </button>
                    </section>
                </aside>
            </div>
        </form>
    @endif
@endsection
