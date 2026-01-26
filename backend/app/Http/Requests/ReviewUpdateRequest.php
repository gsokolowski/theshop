<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // ✅ CHANGED: Middleware handles authentication, controller checks ownership
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'rating' => 'required|numeric|min:1|max:5',
            // ✅ NOTE: product_id and user_id should NOT be in update rules - they shouldn't change
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'body.required' => 'The body field is required.',
            'body.string' => 'The body must be a string.',
            'body.max' => 'The body must not exceed 5000 characters.',
            'rating.required' => 'The rating field is required.',
            'rating.numeric' => 'The rating must be a number.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating must not exceed 5.',
        ];
    }
}