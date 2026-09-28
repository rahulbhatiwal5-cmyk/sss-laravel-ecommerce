<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => $this->trimmedValue('sku'),
            'price' => $this->nullableTrimmedValue('price'),
            'sale_price' => $this->nullableTrimmedValue('sale_price'),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
        ]);
    }

    public function rules(): array
    {
        $skuRule = Rule::unique('product_variants', 'sku');

        if ($variant = $this->variantForProduct()) {
            $skuRule->ignore($variant);
        }

        return [
            'size_id' => ['prohibited'],
            'color_id' => ['prohibited'],
            'transition_action' => ['prohibited'],
            'sku' => ['required', 'string', 'max:255', $skuRule],
            'price' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'low_stock_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('sale_price') && $this->salePriceIsNotLowerThanEffectiveBase()) {
                $validator->errors()->add(
                    'sale_price',
                    'The variant sale price must be lower than its effective base price.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'A variant with this SKU already exists.',
            'price.decimal' => 'The price override may have no more than two decimal places.',
            'price.gt' => 'The price override must be greater than zero.',
            'sale_price.decimal' => 'The sale price override may have no more than two decimal places.',
            'sale_price.gt' => 'The sale price override must be greater than zero.',
        ];
    }

    private function salePriceIsNotLowerThanEffectiveBase(): bool
    {
        $basePrice = $this->filled('price')
            ? $this->input('price')
            : $this->product()->price;

        return $this->cents($this->input('sale_price')) >= $this->cents($basePrice);
    }

    private function variantForProduct(): ?ProductVariant
    {
        $product = $this->product();
        $variant = $this->route('variant');

        if (! $variant instanceof ProductVariant || (string) $variant->product_id !== (string) $product->getKey()) {
            return null;
        }

        return $variant;
    }

    private function product(): Product
    {
        return $this->route('product');
    }

    private function cents(mixed $price): int
    {
        return (int) round(((float) $price) * 100);
    }

    private function trimmedValue(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }

    private function nullableTrimmedValue(string $key): mixed
    {
        $value = $this->trimmedValue($key);

        return $value === '' ? null : $value;
    }
}
