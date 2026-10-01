<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CustomerWishlist;
use App\Services\ProductPriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ShopController extends Controller
{
    public function index(
        Request $request,
        ProductPriceCalculator $priceCalculator,
        CustomerWishlist $wishlists,
    ): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['newest'])],
        ]);

        $now = now();
        $search = trim((string) ($filters['q'] ?? ''));
        $categoryValue = trim((string) ($filters['category'] ?? ''));
        $brandValue = trim((string) ($filters['brand'] ?? ''));
        $selectedCategory = $this->findCategory($categoryValue);
        $selectedBrand = $this->findBrand($brandValue);

        $products = Product::query()
            ->publiclyVisible($now)
            ->with([
                'category:id,name,slug,is_active',
                'brand:id,name,slug,is_active',
                'variants' => fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->select(['id', 'product_id', 'price', 'sale_price', 'stock', 'is_active']),
                'media' => fn (Builder $query) => $query
                    ->where('collection_name', 'main_image')
                    ->orderBy('order_column')
                    ->orderBy('id'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(
                $categoryValue !== '',
                fn (Builder $query) => $selectedCategory
                    ? $query->where('category_id', $selectedCategory->getKey())
                    : $query->whereRaw('1 = 0'),
            )
            ->when(
                $brandValue !== '',
                fn (Builder $query) => $selectedBrand
                    ? $query->where('brand_id', $selectedBrand->getKey())
                    : $query->whereRaw('1 = 0'),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->each(function (Product $product) use ($priceCalculator): void {
            $pricing = $priceCalculator->summarize($product, $product->variants);
            $effectivePrice = $pricing['minimum_effective_price']
                ?? $priceCalculator->productEffectivePrice($product);

            $product->setAttribute('shop_image_url', $this->imageUrl($product->getFirstMedia('main_image')));
            $product->setAttribute('shop_effective_price', $effectivePrice);
            $product->setAttribute('shop_shows_from_price', $pricing['has_differing_effective_prices']);
            $product->setAttribute(
                'shop_is_available',
                $product->variants->contains(
                    fn (ProductVariant $variant): bool => $variant->stock > 0,
                ),
            );
        });

        $wishlistCustomer = $wishlists->activeCustomer();
        $wishlistItemIds = $wishlistCustomer === null
            ? []
            : $wishlists->itemIdsForProducts($wishlistCustomer, $products->getCollection()->modelKeys());

        return view('frontend.shop', [
            'products' => $products,
            'categories' => $this->filterCategories($now),
            'brands' => $this->filterBrands($now),
            'search' => $search,
            'selectedCategoryId' => $selectedCategory?->getKey(),
            'selectedBrandId' => $selectedBrand?->getKey(),
            'hasFilters' => $search !== '' || $categoryValue !== '' || $brandValue !== '',
            'wishlistItemIds' => $wishlistItemIds,
            'canManageWishlist' => $wishlistCustomer !== null,
            'isWishlistGuest' => $request->user('web') === null,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function filterCategories($now)
    {
        return Category::query()
            ->where('is_active', true)
            ->whereHas('products', function (Builder $query) use ($now): void {
                $query
                    ->where('is_active', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', $now);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Brand>
     */
    private function filterBrands($now)
    {
        return Brand::query()
            ->whereHas('products', function (Builder $query) use ($now): void {
                $query
                    ->where('is_active', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', $now)
                    ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);
    }

    private function findCategory(string $value): ?Category
    {
        if ($value === '') {
            return null;
        }

        $slug = Str::slug($value);

        return Category::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($value, $slug): void {
                $query->where('slug', $slug)->orWhere('name', $value);
            })
            ->first(['id', 'name', 'slug']);
    }

    private function findBrand(string $value): ?Brand
    {
        if ($value === '') {
            return null;
        }

        $slug = Str::slug($value);

        return Brand::query()
            ->where(function (Builder $query) use ($value, $slug): void {
                $query->where('slug', $slug)->orWhere('name', $value);
            })
            ->first(['id', 'name', 'slug']);
    }

    private function imageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }
}
