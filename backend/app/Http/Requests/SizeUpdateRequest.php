<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SizeUpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:sizes,name,' . $this->size->id, // unique and except the current size id
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
            'name.required' => 'Size name is required',
            'name.string' => 'Size name must be a valid text',
            'name.max' => 'Size name must not exceed 255 characters',
            'name.unique' => 'This size name already exists',
        ];
    }
}
