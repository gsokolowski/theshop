<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StripePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cartItems' => 'required|array|min:1',
            'cartItems.*.product_id' => 'required|integer|exists:products,id',
            'cartItems.*.qty' => 'required|integer|min:1',
            'cartItems.*.price' => 'required|numeric|min:0',
            'cartItems.*.coupon_id' => 'nullable|integer|exists:coupons,id',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'cartItems.required' => 'Cart items are required',
            'success_url.required' => 'Success URL is required',
            'success_url.url' => 'Success URL must be a valid URL',
            'cancel_url.required' => 'Cancel URL is required',
            'cancel_url.url' => 'Cancel URL must be a valid URL',
        ];
    }
}