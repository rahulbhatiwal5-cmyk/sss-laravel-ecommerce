@php
    $imagesEditable = $imagesEditable ?? false;
    $mainImage = $product->getFirstMedia('main_image');
    $galleryImages = $product->getMedia('gallery');
    $selectedGalleryRemovalIds = collect(old('remove_gallery_media', []))
        ->map(static fn ($id): int => (int) $id)
        ->all();
@endphp

<section class="panel product-media-previews">
    <div class="panel-heading">
        <div>
            <h2>Product photography</h2>
            <p>{{ $imagesEditable ? 'Replace the main image, add gallery images, or mark gallery images for removal.' : 'These existing Spatie images are read-only in this editor.' }}</p>
        </div>
    </div>

    <div class="field">
        <label>Main image</label>
        <figure class="gallery-preview-item product-main-image-preview">
            <img src="{{ $mainImage?->hasGeneratedConversion('medium') ? $mainImage->getUrl('medium') : ($mainImage?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg')) }}"
                alt="{{ $product->name }} main image">
            <figcaption>{{ $mainImage?->name ?? 'No main image is attached.' }}</figcaption>
        </figure>

        @if ($imagesEditable)
            <label class="upload-box mt-3" for="main_image">
                <span class="upload-symbol">Upload</span>
                <strong>Replace the main image</strong>
                <span>Optional &middot; JPEG, PNG, or WebP &middot; up to 2 MB</span>
                <input class="@error('main_image') is-invalid @enderror" id="main_image" name="main_image" type="file"
                    accept="image/jpeg,image/png,image/webp"
                    @error('main_image') aria-invalid="true" aria-describedby="main-image-error" @enderror>
            </label>
            <small>Leave this empty to keep the current main image. Main-image removal is not available.</small>
            @error('main_image')
                <div class="invalid-feedback d-block" id="main-image-error">{{ $message }}</div>
            @enderror
        @endif
    </div>

    <div class="field">
        <label>Gallery images</label>
        @if ($galleryImages->isEmpty())
            <p class="small muted">No gallery images are attached.</p>
        @else
            <div class="gallery-preview-grid" role="list">
                @foreach ($galleryImages as $galleryImage)
                    @php
                        $isMarkedForRemoval = in_array($galleryImage->getKey(), $selectedGalleryRemovalIds, true);
                    @endphp
                    <figure @class(['gallery-preview-item', 'is-marked-for-removal' => $isMarkedForRemoval])
                        role="listitem" data-existing-gallery-image>
                        <img src="{{ $galleryImage->hasGeneratedConversion('medium') ? $galleryImage->getUrl('medium') : $galleryImage->getUrl() }}"
                            alt="{{ $product->name }} gallery image">
                        <figcaption>{{ $galleryImage->name }}</figcaption>

                        @if ($imagesEditable)
                            <input class="visually-hidden" type="checkbox" name="remove_gallery_media[]"
                                value="{{ $galleryImage->getKey() }}" data-gallery-removal-input
                                @checked($isMarkedForRemoval)>
                            <button class="text-link gallery-remove-button" type="button" data-remove-gallery-image
                                aria-pressed="{{ $isMarkedForRemoval ? 'true' : 'false' }}">
                                {{ $isMarkedForRemoval ? 'Keep image' : 'Remove' }}
                            </button>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif

        @if ($imagesEditable)
            <label class="upload-box mt-3" for="gallery">
                <span class="upload-symbol">Upload</span>
                <strong>Add gallery images</strong>
                <span>Optional &middot; JPEG, PNG, or WebP &middot; up to 2 MB each</span>
                <input class="@error('gallery') is-invalid @enderror" id="gallery" name="gallery[]" type="file"
                    accept="image/jpeg,image/png,image/webp" multiple
                    @error('gallery') aria-invalid="true" aria-describedby="gallery-error" @enderror>
            </label>
            <small>Kept gallery images plus new uploads cannot exceed five in total.</small>
            @error('gallery')
                <div class="invalid-feedback d-block" id="gallery-error">{{ $message }}</div>
            @enderror
            @foreach ($errors->get('gallery.*') as $galleryErrors)
                @foreach ($galleryErrors as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            @endforeach
        @endif
    </div>

    @if ($imagesEditable && ($errors->has('main_image') || $errors->has('gallery')))
        <p class="small muted">Please choose replacement or new images again before saving.</p>
    @endif

    @if (! $imagesEditable)
        <p class="small muted">Image upload, replacement, removal, and reordering are not available in this view.</p>
    @endif
</section>
