<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CustomerWishlist;
use App\Services\ProductPriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class WishlistController extends Controller
{
    public function __construct(private readonly CustomerWishlist $wishlists)
    {
    }

    public function index(Request $request, ProductPriceCalculator $priceCalculator): View
    {
        $customer = $this->customer($request);
        $now = now();
        $items = Wishlist::query()
            ->where('user_id', $customer->getKey())
            ->with([
                'product' => fn (Builder $query) => $query
                    ->publiclyVisible($now)
                    ->with([
                        'category:id,is_active',
                        'variants' => fn (Builder $variantQuery) => $variantQuery
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
                            ->select(['id', 'product_id', 'price', 'sale_price', 'stock', 'is_active']),
                        'media' => fn (Builder $mediaQuery) => $mediaQuery
                            ->where('collection_name', 'main_image')
                            ->orderBy('order_column')
                            ->orderBy('id'),
                    ]),
            ])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $items->getCollection()->each(function (Wishlist $item) use ($priceCalculator): void {
            $product = $item->product;

            if ($product === null) {
                return;
            }

            $pricing = $priceCalculator->summarize($product, $product->variants);
            $product->setAttribute(
                'wishlist_effective_price',
                $pricing['minimum_effective_price'] ?? $priceCalculator->productEffectivePrice($product),
            );
            $product->setAttribute('wishlist_shows_from_price', $pricing['has_differing_effective_prices']);
            $product->setAttribute(
                'wishlist_is_available',
                $product->variants->contains(fn (ProductVariant $variant): bool => $variant->stock > 0),
            );
            $product->setAttribute('wishlist_image_url', $this->imageUrl($product->getFirstMedia('main_image')));
        });

        return view('frontend.wishlist', ['items' => $items]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $customer = $this->customer($request);

        try {
            $created = $this->wishlists->save($customer, $product);
        } catch (ModelNotFoundException $exception) {
            // It became unavailable after route binding; expose no product details.
            throw $exception;
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'This product could not be saved right now. Please try again.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'This product could not be saved right now. Please try again.');
        }

        return back()->with('status', $created ? 'Saved to your wishlist.' : 'This product is already saved.');
    }

    public function destroy(Request $request, Wishlist $wishlist): RedirectResponse
    {
        $customer = $this->customer($request);
        // Scope first so a different customer's numeric item ID remains a 404.
        Wishlist::query()
            ->where('user_id', $customer->getKey())
            ->whereKey($wishlist->getKey())
            ->firstOrFail();

        try {
            $this->wishlists->remove($customer, $wishlist);
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'This saved product could not be removed. Please try again.');
        }

        return back()->with('status', 'Removed from your wishlist.');
    }

    private function customer(Request $request): User
    {
        $customer = $this->wishlists->activeCustomer();

        abort_unless($customer instanceof User, 403);

        return $customer;
    }

    private function imageUrl(?Media $media): string
    {
        if ($media?->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $media?->getUrl() ?? asset('sss-admin/images/product-placeholder.svg');
    }
}
