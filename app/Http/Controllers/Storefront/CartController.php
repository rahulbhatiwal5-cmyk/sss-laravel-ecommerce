<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartSummary;
use App\Services\GuestCartManager;
use App\Services\ProductPriceCalculator;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        private readonly GuestCartManager $carts,
        private readonly ProductPriceCalculator $priceCalculator,
        private readonly CartSummary $summary,
    ) {
    }

    public function index(): View
    {
        $carts = $this->carts->currentCarts();
        $this->summary->loadRelations($carts);

        return view('frontend.cart', $this->summary->summarize($carts));
    }

    public function store(StoreCartItemRequest $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->validated('quantity');
        $variantId = (int) $request->validated('variant_id');

        try {
            $outcome = $this->carts->mutate(true, function (Cart $cart) use ($product, $variantId, $quantity): string {
                $lockedProduct = Product::query()
                    ->publiclyVisible(now())
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($lockedProduct === null) {
                    return 'product_unavailable';
                }

                $lockedVariant = $this->selectableVariants($lockedProduct)
                    ->whereKey($variantId)
                    ->lockForUpdate()
                    ->first();

                if ($lockedVariant === null) {
                    return 'variant_unavailable';
                }

                if ($lockedVariant->stock <= 0) {
                    return 'out_of_stock';
                }

                $existingLines = CartItem::query()
                    ->where('cart_id', $cart->getKey())
                    ->where('product_variant_id', $lockedVariant->getKey())
                    ->lockForUpdate()
                    ->get();

                if ($existingLines->count() > 1) {
                    return 'line_conflict';
                }

                /** @var CartItem|null $existingLine */
                $existingLine = $existingLines->first();

                if ($existingLine !== null && (string) $existingLine->product_id !== (string) $lockedProduct->getKey()) {
                    return 'line_conflict';
                }

                $currentQuantity = $existingLine?->quantity ?? 0;

                if ($quantity > $lockedVariant->stock - $currentQuantity) {
                    return 'stock_insufficient';
                }

                $unitPrice = $this->priceCalculator->forVariant($lockedProduct, $lockedVariant)['effective_price'];

                if ($existingLine !== null) {
                    $existingLine->update([
                        'quantity' => $currentQuantity + $quantity,
                        'price' => $unitPrice,
                    ]);
                } else {
                    CartItem::query()->create([
                        'cart_id' => $cart->getKey(),
                        'product_id' => $lockedProduct->getKey(),
                        'product_variant_id' => $lockedVariant->getKey(),
                        'quantity' => $quantity,
                        'price' => $unitPrice,
                    ]);
                }

                return 'added';
            });
        } catch (LockTimeoutException) {
            return back()->withInput()->with('error', 'Your bag is being updated in another request. Please try again.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The item could not be added to your bag. Please try again.');
        }

        if ($outcome !== 'added') {
            return back()->withInput()->withErrors([
                $outcome === 'variant_unavailable' ? 'variant_id' : 'quantity' => $this->addError($outcome),
            ]);
        }

        return to_route('store.cart')->with('status', 'Item added to your bag.');
    }

    public function update(UpdateCartItemRequest $request, string $cartItem): RedirectResponse
    {
        $quantity = (int) $request->validated('quantity');

        try {
            $outcome = $this->carts->mutate(false, function (Cart $cart) use ($cartItem, $quantity): string {
                $itemReference = CartItem::query()
                    ->where('cart_id', $cart->getKey())
                    ->whereKey($cartItem)
                    ->first(['id', 'cart_id', 'product_id', 'product_variant_id']);

                if ($itemReference === null) {
                    return 'not_found';
                }

                if ($itemReference->product_variant_id === null) {
                    return 'variant_unavailable';
                }

                $lockedProduct = Product::query()
                    ->publiclyVisible(now())
                    ->whereKey($itemReference->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedProduct === null) {
                    return 'product_unavailable';
                }

                $lockedVariant = $this->selectableVariants($lockedProduct)
                    ->whereKey($itemReference->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedVariant === null) {
                    return 'variant_unavailable';
                }

                $lockedItem = CartItem::query()
                    ->where('cart_id', $cart->getKey())
                    ->whereKey($cartItem)
                    ->lockForUpdate()
                    ->first();

                if ($lockedItem === null) {
                    return 'not_found';
                }

                if ((string) $lockedItem->product_id !== (string) $lockedProduct->getKey()
                    || (string) $lockedItem->product_variant_id !== (string) $lockedVariant->getKey()) {
                    return 'line_conflict';
                }

                if ($quantity > $lockedVariant->stock) {
                    return 'stock_insufficient';
                }

                $unitPrice = $this->priceCalculator->forVariant($lockedProduct, $lockedVariant)['effective_price'];

                $lockedItem->update([
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                ]);

                return 'updated';
            });
        } catch (LockTimeoutException) {
            return back()->with('error', 'Your bag is being updated in another request. Please try again.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'The item quantity could not be updated. Please try again.');
        }

        if ($outcome === null || $outcome === 'not_found') {
            abort(404);
        }

        if ($outcome !== 'updated') {
            return back()->withErrors(['quantity' => $this->updateError($outcome)]);
        }

        return to_route('store.cart')->with('status', 'Bag quantity updated.');
    }

    public function destroy(string $cartItem): RedirectResponse
    {
        try {
            $outcome = $this->carts->mutate(false, function (Cart $cart) use ($cartItem): string {
                $item = CartItem::query()
                    ->where('cart_id', $cart->getKey())
                    ->whereKey($cartItem)
                    ->lockForUpdate()
                    ->first();

                if ($item === null) {
                    return 'not_found';
                }

                $item->delete();

                return 'removed';
            });
        } catch (LockTimeoutException) {
            return back()->with('error', 'Your bag is being updated in another request. Please try again.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'The item could not be removed from your bag. Please try again.');
        }

        if ($outcome === null || $outcome === 'not_found') {
            abort(404);
        }

        return to_route('store.cart')->with('status', 'Item removed from your bag.');
    }

    private function selectableVariants(Product $product): Builder
    {
        return $product->variants()
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
            });
    }

    private function addError(?string $outcome): string
    {
        return match ($outcome) {
            'product_unavailable' => 'This product is no longer available.',
            'variant_unavailable' => 'Choose a currently available product option.',
            'out_of_stock' => 'This option is out of stock.',
            'stock_insufficient' => 'The requested quantity exceeds the stock currently available.',
            'line_conflict' => 'This bag line changed unexpectedly. Refresh the page and try again.',
            default => 'This item could not be added to your bag.',
        };
    }

    private function updateError(string $outcome): string
    {
        return match ($outcome) {
            'product_unavailable', 'variant_unavailable' => 'This item is no longer available. Remove it from your bag instead.',
            'stock_insufficient' => 'The requested quantity exceeds the stock currently available.',
            'line_conflict' => 'This bag line changed unexpectedly. Refresh the page and try again.',
            default => 'This item quantity could not be updated.',
        };
    }
}
