<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CartSummary
{
    public function __construct(
        private readonly ProductPriceCalculator $priceCalculator,
        private readonly CartMoney $money,
    ) {
    }

    /**
     * Load every relationship needed to price and validate cart lines without
     * N+1 queries. This method does not create, update, or reserve anything.
     *
     * @param  Collection<int, Cart>  $carts
     */
    public function loadRelations(Collection $carts): void
    {
        if ($carts->isEmpty()) {
            return;
        }

        $carts->load([
            'items' => fn (Builder $query) => $query->orderBy('id'),
            'items.product' => fn (Builder $query) => $query->with([
                'category:id,is_active',
                'media' => fn (Builder $mediaQuery) => $mediaQuery
                    ->where('collection_name', 'main_image')
                    ->orderBy('order_column')
                    ->orderBy('id'),
            ]),
            'items.variant' => fn (Builder $query) => $query->with([
                'size:id,name,is_active',
                'color:id,name,code,is_active',
            ]),
        ]);
    }

    /**
     * Recalculate every line from the current product and variant records.
     * Stored cart prices are intentionally not used for the displayed total.
     *
     * @param  Collection<int, Cart>  $carts
     * @return array{items: Collection<int, array<string, mixed>>, itemCount: int, subtotalDisplay: string, problemLineCount: int, isCheckoutReady: bool}
     */
    public function summarize(Collection $carts): array
    {
        $items = $carts->flatMap(fn (Cart $cart) => $cart->items);
        $now = now();
        $subtotalMinor = '0';
        $itemCount = 0;
        $problemLineCount = 0;

        $lines = $items->map(function (CartItem $item) use ($now, &$subtotalMinor, &$itemCount, &$problemLineCount): array {
            $line = $this->presentLine($item, $now);
            $itemCount += $item->quantity;

            if ($line['included_in_subtotal']) {
                $subtotalMinor = $this->money->add($subtotalMinor, $line['line_total_minor']);
            } else {
                $problemLineCount++;
            }

            return $line;
        });

        return [
            'items' => $lines,
            'itemCount' => $itemCount,
            'subtotalDisplay' => $this->money->format($subtotalMinor),
            'problemLineCount' => $problemLineCount,
            'isCheckoutReady' => $lines->isNotEmpty() && $problemLineCount === 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentLine(CartItem $item, DateTimeInterface $now): array
    {
        $product = $item->product;
        $variant = $item->variant;
        $isMatchingVariant = $product !== null
            && $variant !== null
            && (string) $variant->product_id === (string) $product->getKey();
        $pricing = $isMatchingVariant
            ? $this->priceCalculator->forVariant($product, $variant)
            : null;
        $unitPriceMinor = $pricing === null ? null : $this->money->toMinorUnits($pricing['effective_price']);
        $lineTotalMinor = $unitPriceMinor === null
            ? null
            : $this->money->multiply($unitPriceMinor, $item->quantity);
        $productIsPublic = $product?->isPubliclyVisible($now) ?? false;
        $variantIsSelectable = $isMatchingVariant && $this->variantIsSelectable($variant);
        $stock = $variant?->stock ?? 0;
        $canUpdate = $productIsPublic && $variantIsSelectable && $stock > 0;
        $includedInSubtotal = $canUpdate && $item->quantity <= $stock && $lineTotalMinor !== null;
        [$status, $statusMessage] = $this->lineStatus(
            $product,
            $variant,
            $isMatchingVariant,
            $productIsPublic,
            $variantIsSelectable,
            $item->quantity,
        );

        return [
            'id' => $item->getKey(),
            'name' => $product?->name ?? 'Product no longer available',
            'product_url' => $productIsPublic ? route('store.products.show', $product) : null,
            'image_url' => $this->imageUrl($product?->getFirstMedia('main_image')),
            'option_label' => $this->optionLabel($variant),
            'sku' => $variant?->sku ?? '—',
            'quantity' => $item->quantity,
            'quantity_max' => $stock,
            'can_update' => $canUpdate,
            'unit_price_display' => $unitPriceMinor === null ? '—' : $this->money->format($unitPriceMinor),
            'base_price_display' => $pricing !== null && $pricing['sale_price'] !== null
                ? $this->money->format($this->money->toMinorUnits($pricing['base_price']))
                : null,
            'line_total_display' => $lineTotalMinor === null ? '—' : $this->money->format($lineTotalMinor),
            'line_total_minor' => $lineTotalMinor,
            'included_in_subtotal' => $includedInSubtotal,
            'status' => $status,
            'status_message' => $statusMessage,
        ];
    }

    private function variantIsSelectable(ProductVariant $variant): bool
    {
        return $variant->is_active
            && ($variant->size_id === null || $variant->size?->is_active === true)
            && ($variant->color_id === null || $variant->color?->is_active === true);
    }

    /**
     * @return array{string, string}
     */
    private function lineStatus(
        ?Product $product,
        ?ProductVariant $variant,
        bool $isMatchingVariant,
        bool $productIsPublic,
        bool $variantIsSelectable,
        int $quantity,
    ): array {
        if ($product === null) {
            return ['Unavailable', 'This product no longer exists. Remove this line from your bag.'];
        }

        if (! $isMatchingVariant) {
            return ['Unavailable', 'This product option is no longer available. Remove this line from your bag.'];
        }

        if (! $productIsPublic) {
            return ['Unavailable', 'This product is no longer publicly available. Remove this line from your bag.'];
        }

        if (! $variantIsSelectable) {
            return ['Unavailable', 'This selected option is no longer active. Remove this line from your bag.'];
        }

        if ($variant->stock <= 0) {
            return ['Out of stock', 'This option is currently out of stock. You can remove it from your bag.'];
        }

        if ($quantity > $variant->stock) {
            return ['Update quantity', "Only {$variant->stock} currently available. Reduce the quantity to continue."];
        }

        return ['Available', ''];
    }

    private function optionLabel(?ProductVariant $variant): string
    {
        if ($variant === null) {
            return 'Option unavailable';
        }

        $parts = [];

        if ($variant->size?->name) {
            $parts[] = "Size: {$variant->size->name}";
        }

        if ($variant->color?->name) {
            $parts[] = "Colour: {$variant->color->name}";
        }

        return $parts === [] ? 'Default option' : implode(' · ', $parts);
    }

    private function imageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }
}
