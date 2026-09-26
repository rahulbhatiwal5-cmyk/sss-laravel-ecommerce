<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $normalizedName = is_string($name) ? trim($name) : $name;
        $rawSlug = $this->input('slug');
        $slug = $rawSlug;

        if ($rawSlug === null || (is_string($rawSlug) && trim($rawSlug) === '')) {
            $slug = is_string($normalizedName) ? Str::slug($normalizedName) : $normalizedName;
        } elseif (is_string($rawSlug)) {
            $slug = Str::slug(trim($rawSlug));
        }

        $description = $this->input('description');

        $this->merge([
            'name' => $normalizedName,
            'slug' => $slug,
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
            'description' => is_string($description) && trim($description) !== '' ? trim($description) : $description,
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug'),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Enter a name or a slug that can be converted into a valid slug.',
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and single hyphens.',
            'slug.unique' => 'A category with this slug already exists.',
        ];
    }
}
