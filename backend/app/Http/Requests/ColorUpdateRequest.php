<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ColorUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:colors,name,' . $this->color->id, // unique and except the current color id
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Color name is required',
            'name.string' => 'Color name must be a valid text',
            'name.max' => 'Color name must not exceed 255 characters',
            'name.unique' => 'This color name already exists',
        ];
    }
}
