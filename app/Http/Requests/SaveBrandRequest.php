<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $brand = $this->brand();
        $name = $this->trimmedValue('name');
        $slugWasSubmitted = array_key_exists('slug', $this->all());
        $rawSlug = $slugWasSubmitted ? $this->input('slug') : $brand?->slug;
        $slug = $rawSlug;

        if ($slugWasSubmitted && ($rawSlug === null || (is_string($rawSlug) && trim($rawSlug) === ''))) {
            $slug = is_string($name) ? Str::slug($name) : $name;
        } elseif (is_string($rawSlug)) {
            $slug = Str::slug(trim($rawSlug));
        }

        $this->merge([
            'name' => $name,
            'slug' => $slug,
            'logo' => $this->nullableTrimmedValue('logo'),
            'description' => $this->nullableTrimmedValue('description'),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
        ]);
    }

    public function rules(): array
    {
        $slugRule = Rule::unique('brands', 'slug');

        if ($brand = $this->brand()) {
            $slugRule->ignore($brand);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slugRule,
            ],
            'logo' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Enter a name or a slug that can be converted into a valid slug.',
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and single hyphens.',
            'slug.unique' => 'A brand with this slug already exists.',
        ];
    }

    private function brand(): ?Brand
    {
        $brand = $this->route('brand');

        return $brand instanceof Brand ? $brand : null;
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
