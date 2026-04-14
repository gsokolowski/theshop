<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize empty query strings so optional filters do not fail `exists` rules.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['category', 'brand', 'search'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        foreach (['color_id', 'size_id'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'category' => 'nullable|string|max:255|exists:categories,slug',
            'brand' => 'nullable|string|max:255|exists:brands,slug',
            'color_id' => 'nullable|integer|exists:colors,id',
            'size_id' => 'nullable|integer|exists:sizes,id',
            'search' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.exists' => 'The selected category is invalid.',
            'brand.exists' => 'The selected brand is invalid.',
            'color_id.exists' => 'The selected color is invalid.',
            'size_id.exists' => 'The selected size is invalid.',
        ];
    }
}
