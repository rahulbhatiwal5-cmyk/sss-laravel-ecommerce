<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $category = $this->category();
        $name = $this->input('name');
        $normalizedName = is_string($name) ? trim($name) : $name;
        $slugWasSubmitted = array_key_exists('slug', $this->all());
        $rawSlug = $slugWasSubmitted ? $this->input('slug') : $category->slug;
        $slug = $rawSlug;

        if ($slugWasSubmitted && ($rawSlug === null || (is_string($rawSlug) && trim($rawSlug) === ''))) {
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
        $category = $this->category();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($category),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('parent_id')) {
                return;
            }

            $parentId = $this->input('parent_id');

            if ($parentId === null) {
                return;
            }

            $category = $this->category();

            if ((int) $parentId === (int) $category->getKey()) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            if (in_array((int) $parentId, $category->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'A category cannot be moved below one of its descendants.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Enter a name or a slug that can be converted into a valid slug.',
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and single hyphens.',
            'slug.unique' => 'A category with this slug already exists.',
        ];
    }

    private function category(): Category
    {
        return $this->route('category');
    }
}
