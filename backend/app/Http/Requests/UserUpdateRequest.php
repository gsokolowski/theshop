<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            //
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'profile_completed' => 'nullable|boolean',
        ];
    }
    public function messages(): array
    {
        return [
            'name.nullable' => 'The name is required.',
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address must be less than 255 characters.',
            'city.string' => 'The city must be a string.',
            'city.max' => 'The city must be less than 255 characters.',
            'country.string' => 'The country must be a string.',
            'country.max' => 'The country must be less than 255 characters.',
            'zip_code.string' => 'The zip code must be a string.',
            'zip_code.max' => 'The zip code must be less than 255 characters.',
            'phone_number.string' => 'The phone number must be a string.',
            'phone_number.max' => 'The phone number must be less than 255 characters.',
            'profile_image.image' => 'The profile image must be an image.',
            'profile_image.mimes' => 'The profile image must be a file of type: jpeg, png, jpg, gif, svg.',
            'profile_image.max' => 'The profile image must be less than 2MB.',
        ];
    }
}
