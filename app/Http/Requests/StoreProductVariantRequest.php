<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'size_id' => $this->filled('size_id') ? $this->input('size_id') : null,
            'color_id' => $this->filled('color_id') ? $this->input('color_id') : null,
            'sku' => $this->trimmedValue('sku'),
            'price' => $this->nullableTrimmedValue('price'),
            'sale_price' => $this->nullableTrimmedValue('sale_price'),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
            'transition_action' => $this->nullableTrimmedValue('transition_action'),
        ]);
    }

    public function rules(): array
    {
        return [
            'size_id' => [
                'nullable',
                'integer',
                Rule::exists('sizes', 'id')->where('is_active', true),
            ],
            'color_id' => [
                'nullable',
                'integer',
                Rule::exists('colors', 'id')->where('is_active', true),
            ],
            'sku' => ['required', 'string', 'max:255', Rule::unique('product_variants', 'sku')],
            'price' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'low_stock_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
            'transition_action' => [
                Rule::requiredIf($this->requiresDefaultTransition()),
                'nullable',
                Rule::in(['transfer_all', 'deactivate_zero_stock']),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->input('size_id') === null && $this->input('color_id') === null) {
                $validator->errors()->add('size_id', 'Select at least one active size or color.');
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
            'size_id.exists' => 'Select an active size.',
            'color_id.exists' => 'Select an active color.',
            'sku.unique' => 'A variant with this SKU already exists.',
            'price.decimal' => 'The price override may have no more than two decimal places.',
            'price.gt' => 'The price override must be greater than zero.',
            'sale_price.decimal' => 'The sale price override may have no more than two decimal places.',
            'sale_price.gt' => 'The sale price override must be greater than zero.',
            'transition_action.required' => 'Choose how the existing default stock will be handled before adding the first option variant.',
        ];
    }

    private function requiresDefaultTransition(): bool
    {
        $product = $this->product();

        $hasDefaultVariant = $product->variants()
            ->whereNull('size_id')
            ->whereNull('color_id')
            ->exists();
        $hasConfiguredVariant = $product->variants()
            ->where(function ($query): void {
                $query->whereNotNull('size_id')->orWhereNotNull('color_id');
            })
            ->exists();

        return $hasDefaultVariant && ! $hasConfiguredVariant;
    }

    private function salePriceIsNotLowerThanEffectiveBase(): bool
    {
        $basePrice = $this->filled('price')
            ? $this->input('price')
            : $this->product()->price;

        return $this->cents($this->input('sale_price')) >= $this->cents($basePrice);
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
