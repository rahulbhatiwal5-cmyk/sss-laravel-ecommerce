<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $product = $this->product();
        $name = $this->trimmedValue('name');
        $slugWasSubmitted = array_key_exists('slug', $this->all());
        $rawSlug = $slugWasSubmitted ? $this->input('slug') : $product->slug;
        $slug = $rawSlug;

        if ($slugWasSubmitted && ($rawSlug === null || (is_string($rawSlug) && trim($rawSlug) === ''))) {
            $slug = is_string($name) ? Str::slug($name) : $name;
        } elseif (is_string($rawSlug)) {
            $slug = Str::slug(trim($rawSlug));
        }

        $this->merge([
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'brand_id' => $this->filled('brand_id') ? $this->input('brand_id') : null,
            'name' => $name,
            'slug' => $slug,
            'short_description' => $this->nullableTrimmedValue('short_description'),
            'description' => $this->nullableTrimmedValue('description'),
            'material' => $this->nullableTrimmedValue('material'),
            'care_instructions' => $this->nullableTrimmedValue('care_instructions'),
            'gender' => $this->nullableTrimmedValue('gender'),
            'sale_price' => $this->nullableTrimmedValue('sale_price'),
            'variant_sku' => $this->trimmedValue('variant_sku'),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
            'is_featured' => $this->has('is_featured') ? $this->input('is_featured') : false,
        ]);
    }

    public function rules(): array
    {
        $product = $this->product();
        $variantSkuRule = Rule::unique('product_variants', 'sku');

        if ($variant = $this->variantForUniqueRule($product)) {
            $variantSkuRule->ignore($variant);
        }

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'short_description' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'string', 'max:4294967295'],
            'material' => ['nullable', 'string', 'max:65535'],
            'care_instructions' => ['nullable', 'string', 'max:65535'],
            'gender' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'lt:price', 'max:99999999.99'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'variant_sku' => ['required', 'string', 'max:255', $variantSkuRule],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'low_stock_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'main_image' => ['missing'],
            'gallery' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Enter a name or a slug that can be converted into a valid slug.',
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and single hyphens.',
            'slug.unique' => 'A product with this slug already exists.',
            'price.decimal' => 'The base price may have no more than two decimal places.',
            'price.gt' => 'The base price must be greater than zero.',
            'sale_price.decimal' => 'The sale price may have no more than two decimal places.',
            'sale_price.gt' => 'The sale price must be greater than zero.',
            'sale_price.lt' => 'The sale price must be lower than the base price.',
            'variant_sku.unique' => 'A variant with this SKU already exists.',
            'main_image.missing' => 'Images cannot be changed from this form.',
            'gallery.missing' => 'Images cannot be changed from this form.',
        ];
    }

    private function product(): Product
    {
        return $this->route('product');
    }

    private function variantForUniqueRule(Product $product): ?ProductVariant
    {
        return $product->variants()
            ->orderBy('id')
            ->first();
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
