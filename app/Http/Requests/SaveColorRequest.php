<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $code = $this->input('code');

        if (is_string($name)) {
            $name = preg_replace('/\s+/', ' ', trim($name));
        }

        if (is_string($code)) {
            $code = strtoupper(trim($code));

            if ($code === '') {
                $code = null;
            } elseif (preg_match('/^#?([0-9A-F]{3}|[0-9A-F]{6})$/', $code, $matches)) {
                $hex = $matches[1];

                if (strlen($hex) === 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }

                $code = '#'.$hex;
            }
        }

        $this->merge([
            'name' => $name,
            'code' => $code,
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'regex:/^#[0-9A-F]{6}$/'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
