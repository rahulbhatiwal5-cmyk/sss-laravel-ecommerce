<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
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
            'variants' => fn ($query) => $query->orderBy('id'),
        ]);

        $defaultVariant = $this->editableDefaultVariant($product->variants);

        return view('sss-admin.products.create', [
            'categories' => $this->productCategories(),
            'brands' => $this->productBrands(),
            'canCreateProduct' => true,
            'product' => $product,
            'defaultVariant' => $defaultVariant,
            'isEditing' => true,
            'variantConfigurationMessage' => $defaultVariant ? null : $this->unsupportedVariantMessage(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $wasUpdated = DB::transaction(function () use ($request, $product): bool {
                $lockedProduct = Product::query()
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $defaultVariant = $this->editableDefaultVariant(
                    $lockedProduct->variants()
                        ->lockForUpdate()
                        ->orderBy('id')
                        ->get(),
                );

                if (! $defaultVariant) {
                    return false;
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

                $defaultVariant->update([
                    'sku' => $request->validated('variant_sku'),
                    'stock' => $request->validated('stock'),
                    'low_stock_limit' => $request->validated('low_stock_limit'),
                ]);

                return true;
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'The product could not be updated. Please try again.');
        }

        if (! $wasUpdated) {
            return back()
                ->withInput()
                ->with('error', $this->unsupportedVariantMessage());
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

    /**
     * @return Collection<int, Category>
     */
    private function productCategories(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    /**
     * @return Collection<int, Brand>
     */
    private function productBrands(): Collection
    {
        return Brand::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     */
    private function editableDefaultVariant(Collection $variants): ?ProductVariant
    {
        if ($variants->count() !== 1) {
            return null;
        }

        /** @var ProductVariant $variant */
        $variant = $variants->first();

        if (
            $variant->color_id !== null ||
            $variant->size_id !== null ||
            $variant->price !== null ||
            $variant->sale_price !== null
        ) {
            return null;
        }

        return $variant;
    }

    private function unsupportedVariantMessage(): string
    {
        return 'This product cannot be edited here because it must have exactly one default variant with no size, colour, or variant price overrides.';
    }

    private function mainImage(StoreProductRequest $request): UploadedFile
    {
        $mainImage = $request->file('main_image');

        if (! $mainImage instanceof UploadedFile) {
            throw new \LogicException('The validated main image could not be read.');
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
}
