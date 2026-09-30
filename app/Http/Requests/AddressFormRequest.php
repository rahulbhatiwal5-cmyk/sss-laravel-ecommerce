<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AddressFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The addresses migration uses Laravel's default string length (255) for
     * text fields, except phone and postal_code which are limited to 20.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
            'is_default' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        foreach ([
            'type',
            'name',
            'phone',
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country',
        ] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $values[$field] = trim($value);
            }
        }

        foreach (['address_line_2', 'landmark'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = trim($value);
                $values[$field] = $value === '' ? null : $value;
            }
        }

        $values['is_default'] = $this->boolean('is_default');

        $this->merge($values);
    }
}
