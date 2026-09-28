<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductMediaUploader;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ProductController extends Controller
{
    public function create(): View
    {
        $categories = $this->productCategories();

        return view('sss-admin.products.create', [
            'categories' => $categories,
            'brands' => $this->productBrands(),
            'canCreateProduct' => $categories->isNotEmpty(),
            'product' => null,
            'defaultVariant' => null,
            'isEditing' => false,
            'variantConfigurationMessage' => null,
        ]);
    }

    public function store(
        StoreProductRequest $request,
        ProductMediaUploader $mediaUploader,
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request, $mediaUploader): void {
                $product = Product::query()->create([
                    ...$request->safe()->only([
                        'category_id',
                        'brand_id',
                        'name',
                        'slug',
                        'short_description',
                        'description',
                        'material',
                        'care_instructions',
                        'gender',
                        'price',
                        'sale_price',
                        'is_active',
                        'is_featured',
                    ]),
                    'sku' => null,
                    'published_at' => $request->boolean('is_active') ? now() : null,
                ]);

                $product->variants()->create([
                    'color_id' => null,
                    'size_id' => null,
                    'sku' => $request->validated('variant_sku'),
                    'price' => null,
                    'sale_price' => null,
                    'stock' => $request->validated('stock'),
                    'low_stock_limit' => $request->validated('low_stock_limit'),
                    'is_active' => true,
                ]);

                $mediaUploader->store(
                    $product,
                    $this->mainImage($request),
                    $this->galleryImages($request),
                );
            });
        } catch (Throwable $exception) {
            $mediaUploader->cleanup();
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'The product was not saved. Please select the images again and try once more.')
                ->withErrors([
                    'main_image' => 'The product images could not be stored. Please select them again and try once more.',
                ]);
        }

        return to_route('admin.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'media' => fn ($query) => $query
                ->whereIn('collection_name', ['main_image', 'gallery'])
                ->orderBy('order_column')
                ->orderBy('id'),
        ]);

        return view('sss-admin.products.create', [
            'categories' => $this->productCategories(),
            'brands' => $this->productBrands(),
            'canCreateProduct' => true,
            'product' => $product,
            'isEditing' => true,
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductMediaUploader $mediaUploader,
    ): RedirectResponse {
        try {
            $rawGalleryMediaIds = $request->validated('remove_gallery_media', []);
            $galleryMediaIdsForRemoval = is_array($rawGalleryMediaIds)
                ? array_map('intval', $rawGalleryMediaIds)
                : [];
            $replacementMainImage = $this->replacementMainImage($request);
            $newGalleryImages = $this->newGalleryImages($request);

            $mediaUploader->begin();

            $outcome = DB::transaction(function () use (
                $request,
                $product,
                $mediaUploader,
                $replacementMainImage,
                $newGalleryImages,
                $galleryMediaIdsForRemoval,
            ): string {
                $lockedProduct = Product::query()
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $media = $lockedProduct->media()
                    ->whereIn('collection_name', ['main_image', 'gallery'])
                    ->lockForUpdate()
                    ->orderBy('order_column')
                    ->orderBy('id')
                    ->get();
                $mainImages = $media->where('collection_name', 'main_image');
                $galleryImages = $media->where('collection_name', 'gallery');

                if ($mainImages->count() > 1) {
                    return 'invalid_main_image_configuration';
                }

                if ($mainImages->isEmpty() && ! $replacementMainImage) {
                    return 'main_image_required';
                }

                $galleryImagesForRemoval = $galleryImages->whereIn('id', $galleryMediaIdsForRemoval);

                if ($galleryImagesForRemoval->count() !== count($galleryMediaIdsForRemoval)) {
                    return 'invalid_gallery_removal';
                }

                if ($galleryImages->count() - $galleryImagesForRemoval->count() + count($newGalleryImages) > 5) {
                    return 'gallery_limit';
                }

                $productAttributes = $request->safe()->only([
                    'category_id',
                    'brand_id',
                    'name',
                    'slug',
                    'short_description',
                    'description',
                    'material',
                    'care_instructions',
                    'gender',
                    'price',
                    'sale_price',
                    'is_active',
                    'is_featured',
                ]);

                if (! $request->boolean('is_active')) {
                    $productAttributes['published_at'] = null;
                } elseif ($lockedProduct->published_at === null) {
                    $productAttributes['published_at'] = now();
                }

                $lockedProduct->update($productAttributes);

                $mediaUploader->addGallery($lockedProduct, $newGalleryImages);

                if ($replacementMainImage) {
                    // The single-file collection removes the old main image only after this new one succeeds.
                    // Keep this as the final upload so no later upload failure can leave the product without one.
                    $mediaUploader->replaceMain($lockedProduct, $replacementMainImage);
                }

                return 'updated';
            });
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $mediaUploader->cleanup();
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'The product could not be updated. Please choose new images again and try once more.');
        }

        if ($outcome !== 'updated') {
            return back()
                ->withInput()
                ->with('error', $this->updateBlockedMessage($outcome));
        }

        try {
            $this->removeSelectedGalleryMedia($product, $galleryMediaIdsForRemoval);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('admin.products.edit', $product)->with(
                'error',
                'Product details and new images were saved, but one or more selected gallery images could not be removed. Please review the gallery and try again.',
            );
        }

        return to_route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $status = $filters['status'] ?? null;

        $products = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'media' => fn ($query) => $query->where('collection_name', 'main_image'),
            ])
            ->withCount('variants')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('is_active', $status === 'active'),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $products->getCollection()->each(function (Product $product): void {
            $product->setAttribute(
                'listing_image_url',
                $this->listingImageUrl($product->getFirstMedia('main_image')),
            );
        });

        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        return view('sss-admin.products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'status',
        ));
    }

    private function listingImageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }

    private function productCategories(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    private function productBrands(): Collection
    {
        return Brand::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    private function updateBlockedMessage(string $outcome): string
    {
        return match ($outcome) {
            'invalid_main_image_configuration' => 'This product has an unsupported main image configuration. It must have exactly one main image before it can be edited here.',
            'main_image_required' => 'Choose a replacement main image. A product must always have one main image.',
            'invalid_gallery_removal' => 'One of the selected gallery images is no longer available for this product. Refresh the page and try again.',
            'gallery_limit' => 'Keep no more than five gallery images in total.',
            default => 'The product could not be updated. Please try again.',
        };
    }

    /**
     * @param  array<int, int>  $mediaIds
     */
    private function removeSelectedGalleryMedia(Product $product, array $mediaIds): void
    {
        if ($mediaIds === []) {
            return;
        }

        $galleryMedia = Media::query()
            ->where('model_type', $product->getMorphClass())
            ->where('model_id', $product->getKey())
            ->where('collection_name', 'gallery')
            ->whereIn('id', $mediaIds)
            ->orderBy('id')
            ->get();

        if ($galleryMedia->count() !== count($mediaIds)) {
            throw new \LogicException('A selected gallery image is no longer available for this product.');
        }

        foreach ($galleryMedia as $galleryImage) {
            $galleryImage->delete();
        }
    }

    private function mainImage(StoreProductRequest $request): UploadedFile
    {
        $mainImage = $request->file('main_image');

        if (! $mainImage instanceof UploadedFile) {
            throw new \LogicException('The validated main image could not be read.');
        }

        return $mainImage;
    }

    private function replacementMainImage(UpdateProductRequest $request): ?UploadedFile
    {
        $mainImage = $request->file('main_image');

        if ($mainImage === null) {
            return null;
        }

        if (! $mainImage instanceof UploadedFile) {
            throw new \LogicException('The validated replacement main image could not be read.');
        }

        return $mainImage;
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function galleryImages(StoreProductRequest $request): array
    {
        $galleryImages = $request->file('gallery', []);

        if (! is_array($galleryImages)) {
            throw new \LogicException('The validated gallery images could not be read.');
        }

        foreach ($galleryImages as $galleryImage) {
            if (! $galleryImage instanceof UploadedFile) {
                throw new \LogicException('The validated gallery image could not be read.');
            }
        }

        return $galleryImages;
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function newGalleryImages(UpdateProductRequest $request): array
    {
        $galleryImages = $request->file('gallery', []);

        if (! is_array($galleryImages)) {
            throw new \LogicException('The validated gallery images could not be read.');
        }

        foreach ($galleryImages as $galleryImage) {
            if (! $galleryImage instanceof UploadedFile) {
                throw new \LogicException('The validated gallery image could not be read.');
            }
        }

        return $galleryImages;
    }
}
