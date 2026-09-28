<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ProductVariantController extends Controller
{
    public function index(Product $product): View
    {
        $product->load([
            'variants' => fn ($query) => $query
                ->with(['size:id,name', 'color:id,name,code'])
                ->orderBy('id'),
        ]);

        $defaultVariant = $product->variants->first(fn (ProductVariant $variant): bool => $this->isDefaultVariant($variant));
        $hasConfiguredVariants = $product->variants->contains(
            fn (ProductVariant $variant): bool => ! $this->isDefaultVariant($variant),
        );

        return view('sss-admin.products.variants.index', [
            'product' => $product,
            'sizes' => Size::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name']),
            'colors' => Color::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'code']),
            'defaultVariant' => $defaultVariant,
            'requiresTransition' => $defaultVariant !== null && ! $hasConfiguredVariants,
        ]);
    }

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        try {
            $outcome = DB::transaction(function () use ($request, $product): string {
                $lockedProduct = Product::query()
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $variants = $lockedProduct->variants()
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->get();

                $sizeId = $request->validated('size_id');
                $colorId = $request->validated('color_id');

                if ($sizeId !== null && ! Size::query()->whereKey($sizeId)->where('is_active', true)->exists()) {
                    return 'size_not_active';
                }

                if ($colorId !== null && ! Color::query()->whereKey($colorId)->where('is_active', true)->exists()) {
                    return 'color_not_active';
                }

                if ($this->combinationExists($variants, $sizeId, $colorId)) {
                    return 'duplicate_combination';
                }

                $defaultVariant = $variants->first(
                    fn (ProductVariant $variant): bool => $this->isDefaultVariant($variant),
                );
                $hasConfiguredVariants = $variants->contains(
                    fn (ProductVariant $variant): bool => ! $this->isDefaultVariant($variant),
                );

                if (! $hasConfiguredVariants && $defaultVariant) {
                    $transitionOutcome = $this->transitionDefaultVariant(
                        $request,
                        $defaultVariant,
                    );

                    if ($transitionOutcome !== 'transitioned') {
                        return $transitionOutcome;
                    }
                } elseif ($hasConfiguredVariants && $defaultVariant?->is_active) {
                    return 'default_variant_must_be_inactive';
                }

                $lockedProduct->variants()->create([
                    'size_id' => $sizeId,
                    'color_id' => $colorId,
                    'sku' => $request->validated('sku'),
                    'price' => $request->validated('price'),
                    'sale_price' => $request->validated('sale_price'),
                    'stock' => (int) $request->validated('stock'),
                    'low_stock_limit' => (int) $request->validated('low_stock_limit'),
                    'is_active' => $request->boolean('is_active'),
                ]);

                return 'created';
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'sku' => 'This SKU or option combination was just saved by another request. Refresh and try again.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The variant could not be created. Please try again.');
        }

        if ($outcome !== 'created') {
            return back()->withInput()->withErrors($this->storeBlockedErrors($outcome));
        }

        return to_route('admin.products.variants.index', $product)->with('status', 'Variant created successfully.');
    }

    public function edit(Product $product, ProductVariant $variant): View
    {
        $variant = $this->variantForProduct($product, $variant);
        $variant->load(['size:id,name', 'color:id,name,code']);

        return view('sss-admin.products.variants.edit', compact('product', 'variant'));
    }

    public function update(
        UpdateProductVariantRequest $request,
        Product $product,
        ProductVariant $variant,
    ): RedirectResponse {
        $this->variantForProduct($product, $variant);

        try {
            $outcome = DB::transaction(function () use ($request, $product, $variant): string {
                $lockedProduct = Product::query()
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $variants = $lockedProduct->variants()
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->get();
                $lockedVariant = $variants->firstWhere('id', $variant->getKey());

                if (! $lockedVariant instanceof ProductVariant) {
                    throw (new ModelNotFoundException())->setModel(ProductVariant::class, [$variant->getKey()]);
                }

                $hasConfiguredVariants = $variants->contains(
                    fn (ProductVariant $existing): bool => ! $this->isDefaultVariant($existing),
                );
                $attributes = [
                    'sku' => $request->validated('sku'),
                    'price' => $request->validated('price'),
                    'sale_price' => $request->validated('sale_price'),
                    'stock' => (int) $request->validated('stock'),
                    'low_stock_limit' => (int) $request->validated('low_stock_limit'),
                    'is_active' => $request->boolean('is_active'),
                ];

                if ($hasConfiguredVariants && $this->isDefaultVariant($lockedVariant)) {
                    if ($attributes['is_active']) {
                        return 'default_variant_must_be_inactive';
                    }

                    if ($attributes['stock'] !== 0) {
                        return 'default_variant_stock_must_be_zero';
                    }
                }

                $lockedVariant->update($attributes);

                return 'updated';
            }, 3);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'sku' => 'This SKU was just saved by another request. Refresh and try again.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The variant could not be updated. Please try again.');
        }

        if ($outcome !== 'updated') {
            return back()->withInput()->withErrors($this->updateBlockedErrors($outcome));
        }

        return to_route('admin.products.variants.index', $product)->with('status', 'Variant updated successfully.');
    }

    private function transitionDefaultVariant(
        StoreProductVariantRequest $request,
        ProductVariant $defaultVariant,
    ): string {
        return match ($request->validated('transition_action')) {
            'transfer_all' => $this->transferDefaultStock($request, $defaultVariant),
            'deactivate_zero_stock' => $this->deactivateEmptyDefaultVariant($defaultVariant),
            default => 'transition_required',
        };
    }

    private function transferDefaultStock(
        StoreProductVariantRequest $request,
        ProductVariant $defaultVariant,
    ): string {
        if ((int) $request->validated('stock') !== $defaultVariant->stock) {
            return 'transition_stock_mismatch';
        }

        $defaultVariant->update([
            'stock' => 0,
            'is_active' => false,
        ]);

        return 'transitioned';
    }

    private function deactivateEmptyDefaultVariant(ProductVariant $defaultVariant): string
    {
        if ($defaultVariant->stock !== 0) {
            return 'default_stock_not_zero';
        }

        $defaultVariant->update(['is_active' => false]);

        return 'transitioned';
    }

    private function combinationExists($variants, ?int $sizeId, ?int $colorId): bool
    {
        return $variants->contains(
            fn (ProductVariant $variant): bool => (string) $variant->size_id === (string) $sizeId
                && (string) $variant->color_id === (string) $colorId,
        );
    }

    private function isDefaultVariant(ProductVariant $variant): bool
    {
        return $variant->size_id === null && $variant->color_id === null;
    }

    private function variantForProduct(Product $product, ProductVariant $variant): ProductVariant
    {
        if ((string) $variant->product_id !== (string) $product->getKey()) {
            abort(404);
        }

        return $variant;
    }

    /**
     * @return array<string, string>
     */
    private function storeBlockedErrors(string $outcome): array
    {
        return match ($outcome) {
            'duplicate_combination' => [
                'size_id' => 'This size and color combination already exists for this product.',
            ],
            'size_not_active' => [
                'size_id' => 'The selected size is no longer active. Refresh the page and choose an active size.',
            ],
            'color_not_active' => [
                'color_id' => 'The selected color is no longer active. Refresh the page and choose an active color.',
            ],
            'transition_stock_mismatch' => [
                'stock' => 'To transfer the default stock, enter exactly the current default stock quantity.',
            ],
            'default_stock_not_zero' => [
                'transition_action' => 'Set the default variant stock to zero before deactivating it without a transfer.',
            ],
            'default_variant_must_be_inactive' => [
                'transition_action' => 'The default variant must be inactive once size or color variants exist. Update it to zero stock and inactive first.',
            ],
            default => [
                'transition_action' => 'Choose how the existing default stock will be handled before adding the first option variant.',
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function updateBlockedErrors(string $outcome): array
    {
        return match ($outcome) {
            'default_variant_stock_must_be_zero' => [
                'stock' => 'The default variant must have zero stock once size or color variants exist.',
            ],
            default => [
                'is_active' => 'The default variant must remain inactive once size or color variants exist.',
            ],
        };
    }
}
