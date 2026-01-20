<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // User must be authenticated via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cartItems' => 'required|array|min:1',
            'cartItems.*.product_id' => 'required|integer|exists:products,id',
            'cartItems.*.qty' => 'required|integer|min:1',
            'cartItems.*.price' => 'required|numeric|min:0',
            'cartItems.*.coupon_id' => 'nullable|integer|exists:coupons,id',
        ];      
    }
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cartItems.required' => 'Cart items are required',
            'cartItems.array' => 'Cart items must be an array',
            'cartItems.min' => 'At least one cart item is required',
            'cartItems.*.product_id.required' => 'Product ID is required for each cart item',
            'cartItems.*.product_id.integer' => 'Product ID must be an integer',
            'cartItems.*.product_id.exists' => 'Invalid product ID',
            'cartItems.*.qty.required' => 'Quantity is required for each cart item',
            'cartItems.*.qty.integer' => 'Quantity must be an integer',
            'cartItems.*.qty.min' => 'Quantity must be at least 1',
            'cartItems.*.price.required' => 'Price is required for each cart item',
            'cartItems.*.price.numeric' => 'Price must be a number',
            'cartItems.*.price.min' => 'Price must be greater than or equal to 0',
            'cartItems.*.coupon_id.integer' => 'Coupon ID must be an integer',
            'cartItems.*.coupon_id.exists' => 'Invalid coupon ID',
        ];
    }
}
