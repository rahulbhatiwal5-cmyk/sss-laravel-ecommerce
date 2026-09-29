<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductPriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    public function show(Product $product, ProductPriceCalculator $priceCalculator): View
    {
        $product = Product::query()
            ->publiclyVisible(now())
            ->whereKey($product->getKey())
            ->with([
                'category:id,name,slug,is_active',
                'brand:id,name,slug',
                'variants' => fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->where(function (Builder $query): void {
                        $query->whereNull('size_id')->orWhereHas(
                            'size',
                            fn (Builder $sizeQuery) => $sizeQuery->where('is_active', true),
                        );
                    })
                    ->where(function (Builder $query): void {
                        $query->whereNull('color_id')->orWhereHas(
                            'color',
                            fn (Builder $colorQuery) => $colorQuery->where('is_active', true),
                        );
                    })
                    ->with([
                        'size:id,name,sort_order,is_active',
                        'color:id,name,code,is_active',
                    ])
                    ->select([
                        'id',
                        'product_id',
                        'size_id',
                        'color_id',
                        'sku',
                        'price',
                        'sale_price',
                        'stock',
                        'is_active',
                    ])
                    ->orderBy('id'),
                'media' => fn (Builder $query) => $query
                    ->whereIn('collection_name', ['main_image', 'gallery'])
                    ->orderBy('collection_name')
                    ->orderBy('order_column')
                    ->orderBy('id'),
            ])
            ->firstOrFail();

        $activeVariants = $product->variants;
        $optionVariants = $activeVariants
            ->filter(fn (ProductVariant $variant): bool => $variant->size_id !== null || $variant->color_id !== null)
            ->values();
        $hasOptions = $optionVariants->isNotEmpty();
        $selectableVariants = $hasOptions ? $optionVariants : $activeVariants;
        $defaultVariant = $selectableVariants->first(
            fn (ProductVariant $variant): bool => $variant->size_id === null && $variant->color_id === null,
        );
        $isSimpleProduct = ! $hasOptions
            && $selectableVariants->count() === 1
            && $defaultVariant instanceof ProductVariant;

        $variantData = $selectableVariants
            ->map(fn (ProductVariant $variant): array => $this->variantData($product, $variant, $priceCalculator))
            ->values();
        $initialVariant = $isSimpleProduct ? $variantData->first() : null;
        $pricingSummary = $priceCalculator->summarize($product, $selectableVariants);
        $summaryPrice = $pricingSummary['minimum_effective_price']
            ?? $priceCalculator->productEffectivePrice($product);

        return view('frontend.products.show', [
            'product' => $product,
            'mainImageUrl' => $this->imageUrl($product->getFirstMedia('main_image')),
            'galleryImages' => $product->getMedia('gallery')->map(fn (Media $media): array => [
                'url' => $this->imageUrl($media),
                'alt' => "{$product->name} gallery image",
            ]),
            'variantData' => $variantData,
            'hasOptions' => $hasOptions,
            'isSimpleProduct' => $isSimpleProduct,
            'initialVariant' => $initialVariant,
            'summaryPriceDisplay' => $this->money($summaryPrice),
            'summaryShowsFromPrice' => ! $isSimpleProduct && $pricingSummary['has_differing_effective_prices'],
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     label: string,
     *     size_id: ?int,
     *     color_id: ?int,
     *     sku: string,
     *     stock: int,
     *     is_available: bool,
     *     base_price: mixed,
     *     sale_price: mixed,
     *     effective_price: mixed,
     *     base_price_display: string,
     *     sale_price_display: ?string,
     *     effective_price_display: string
     * }
     */
    private function variantData(
        Product $product,
        ProductVariant $variant,
        ProductPriceCalculator $priceCalculator,
    ): array {
        $pricing = $priceCalculator->forVariant($product, $variant);

        return [
            'id' => (int) $variant->getKey(),
            'label' => $this->variantLabel($variant),
            'size_id' => $variant->size_id === null ? null : (int) $variant->size_id,
            'color_id' => $variant->color_id === null ? null : (int) $variant->color_id,
            'sku' => $variant->sku,
            'stock' => (int) $variant->stock,
            'is_available' => $variant->stock > 0,
            'base_price' => $pricing['base_price'],
            'sale_price' => $pricing['sale_price'],
            'effective_price' => $pricing['effective_price'],
            'base_price_display' => $this->money($pricing['base_price']),
            'sale_price_display' => $pricing['sale_price'] === null ? null : $this->money($pricing['sale_price']),
            'effective_price_display' => $this->money($pricing['effective_price']),
        ];
    }

    private function variantLabel(ProductVariant $variant): string
    {
        return collect([$variant->size?->name, $variant->color?->name])
            ->filter(fn (?string $value): bool => $value !== null && $value !== '')
            ->implode(' / ')
            ?: 'Default option';
    }

    private function imageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('medium')) {
            return $media->getUrl('medium');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }

    private function money(mixed $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }
}
