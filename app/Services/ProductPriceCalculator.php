<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductPriceCalculator
{
    /**
     * @return array{base_price: mixed, sale_price: mixed, effective_price: mixed, inherits_base_price: bool}
     */
    public function forVariant(Product $product, ProductVariant $variant): array
    {
        $inheritsBasePrice = $variant->price === null;
        $basePrice = $inheritsBasePrice ? $product->price : $variant->price;
        $salePrice = $variant->sale_price !== null
            ? $variant->sale_price
            : ($inheritsBasePrice ? $product->sale_price : null);

        return [
            'base_price' => $basePrice,
            'sale_price' => $salePrice,
            'effective_price' => $salePrice !== null ? $salePrice : $basePrice,
            'inherits_base_price' => $inheritsBasePrice,
        ];
    }

    /**
     * @param  iterable<ProductVariant>  $variants
     * @return array{minimum_effective_price: mixed, maximum_effective_price: mixed, has_differing_effective_prices: bool, variant_count: int}
     */
    public function summarize(Product $product, iterable $variants): array
    {
        $minimum = null;
        $maximum = null;
        $count = 0;

        foreach ($variants as $variant) {
            $price = $this->forVariant($product, $variant)['effective_price'];

            if ($minimum === null || $this->cents($price) < $this->cents($minimum)) {
                $minimum = $price;
            }

            if ($maximum === null || $this->cents($price) > $this->cents($maximum)) {
                $maximum = $price;
            }

            $count++;
        }

        return [
            'minimum_effective_price' => $minimum,
            'maximum_effective_price' => $maximum,
            'has_differing_effective_prices' => $minimum !== null
                && $maximum !== null
                && $this->cents($minimum) !== $this->cents($maximum),
            'variant_count' => $count,
        ];
    }

    public function productEffectivePrice(Product $product): mixed
    {
        return $product->sale_price !== null ? $product->sale_price : $product->price;
    }

    private function cents(mixed $amount): int
    {
        return (int) str_replace('.', '', number_format((float) $amount, 2, '.', ''));
    }
}
