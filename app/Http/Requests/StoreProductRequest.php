<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->trimmedValue('name');
        $rawSlug = $this->input('slug');
        $slug = $rawSlug;

        if ($rawSlug === null || (is_string($rawSlug) && trim($rawSlug) === '')) {
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
                Rule::unique('products', 'slug'),
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
            'variant_sku' => ['required', 'string', 'max:255', Rule::unique('product_variants', 'sku')],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'low_stock_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'main_image' => array_merge(['required'], $this->imageRules()),
            'gallery' => ['nullable', 'array', 'max:5'],
            'gallery.*' => array_merge(['required'], $this->imageRules()),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! Category::query()->exists()) {
                $validator->errors()->add(
                    'category_id',
                    'Create a category before adding a product.',
                );
            }
        });
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
            'main_image.required' => 'Choose a main product image.',
            'main_image.mimes' => 'The main image must be a JPEG, PNG, or WebP file.',
            'main_image.dimensions' => 'Images must be at least 100 × 100 pixels and no larger than 6000 × 8000 pixels.',
            'gallery.max' => 'You can upload at most five gallery images.',
            'gallery.*.mimes' => 'Each gallery image must be a JPEG, PNG, or WebP file.',
            'gallery.*.dimensions' => 'Images must be at least 100 × 100 pixels and no larger than 6000 × 8000 pixels.',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function imageRules(): array
    {
        return [
            File::image()
                ->max('2mb')
                ->dimensions(
                    Rule::dimensions()
                        ->minWidth(100)
                        ->minHeight(100)
                        ->maxWidth(6000)
                        ->maxHeight(8000),
                ),
            'mimes:jpg,jpeg,png,webp',
        ];
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
