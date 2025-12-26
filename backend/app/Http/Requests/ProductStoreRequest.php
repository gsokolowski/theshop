<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:products,name',
            'qty' => 'required|integer',
            'price' => 'required|integer',
            'description' => 'required|string|max:5000',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'first_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'second_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'third_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|boolean',
            'category_id' => 'required',
            'brand_id' => 'required',
            'color_id' => 'required|array',
            'size_id' => 'required|array',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'name.max' => 'The name field must be less than 255 characters.',
            'name.unique' => 'The name field must be unique.',
            'qty.required' => 'The quantity field is required.',
            'qty.integer' => 'The quantity field must be an integer.',
            'price.required' => 'The price field is required.',
            'price.integer' => 'The price field must be an integer.',
            'description.required' => 'The description field is required.',
            'description.string' => 'The description field must be a string.',
            'description.max' => 'The description field must be less than 5000 characters.',
            'thumbnail.required' => 'The thumbnail field is required.',
            'thumbnail.image' => 'The thumbnail field must be an image.',
            'thumbnail.mimes' => 'The thumbnail field must be a jpeg, png, jpg, gif, or svg.',
            'thumbnail.max' => 'The thumbnail field must be less than 2048KB.',
            'first_image.image' => 'The first image field must be an image.',
            'first_image.mimes' => 'The first image field must be a jpeg, png, jpg, gif, or svg.',
            'first_image.max' => 'The first image field must be less than 2048KB.',
            'second_image.image' => 'The second image field must be an image.',
            'second_image.mimes' => 'The second image field must be a jpeg, png, jpg, gif, or svg.',
            'second_image.max' => 'The second image field must be less than 2048KB.',
            'third_image.image' => 'The third image field must be an image.',
            'third_image.mimes' => 'The third image field must be a jpeg, png, jpg, gif, or svg.',
            'third_image.max' => 'The third image field must be less than 2048KB.',
            'status.required' => 'The status field is required.',
            'status.boolean' => 'The status field must be a boolean.',
            'category_id.required' => 'The category field is required.',
            'brand_id.required' => 'The brand field is required.',
            'color_id.required' => 'The color field is required.',
        ];
    }
}
