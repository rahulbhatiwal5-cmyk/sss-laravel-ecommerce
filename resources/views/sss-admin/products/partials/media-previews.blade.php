@php
    $mainImage = $product->getFirstMedia('main_image');
    $galleryImages = $product->getMedia('gallery');
@endphp

<section class="panel product-media-previews">
    <div class="panel-heading">
        <div>
            <h2>Product photography</h2>
            <p>These existing Spatie images are read-only in this editor.</p>
        </div>
    </div>

    <div class="field">
        <label>Main image</label>
        <figure class="gallery-preview-item">
            <img src="{{ $mainImage?->hasGeneratedConversion('medium') ? $mainImage->getUrl('medium') : ($mainImage?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg')) }}"
                alt="{{ $product->name }} main image">
            <figcaption>{{ $mainImage?->name ?? 'No main image is attached.' }}</figcaption>
        </figure>
        @error('main_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label>Gallery images</label>
        @if ($galleryImages->isEmpty())
            <p class="small muted">No gallery images are attached.</p>
        @else
            <div class="gallery-preview-grid" role="list">
                @foreach ($galleryImages as $galleryImage)
                    <figure class="gallery-preview-item" role="listitem">
                        <img src="{{ $galleryImage->hasGeneratedConversion('medium') ? $galleryImage->getUrl('medium') : $galleryImage->getUrl() }}"
                            alt="{{ $product->name }} gallery image">
                        <figcaption>{{ $galleryImage->name }}</figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
        @error('gallery')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <p class="small muted">Image upload, replacement, removal, and reordering are not available in this step.</p>
</section>
