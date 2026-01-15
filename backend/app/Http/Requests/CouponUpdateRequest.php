<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:coupons,name,' . $this->coupon->id,
            'discount' => 'required|numeric|min:0|max:100',
            'valid_until' => 'required|date',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Coupon name is required',
            'name.string' => 'Coupon name must be a valid text',
            'name.max' => 'Coupon name must not exceed 255 characters',
            'name.unique' => 'This coupon name already exists',
            'discount.required' => 'Discount is required',
            'discount.numeric' => 'Discount must be a valid number',
            'discount.min' => 'Discount must be at least 0',
            'discount.max' => 'Discount must be at most 100',
            'valid_until.required' => 'Valid until is required',
            'valid_until.date' => 'Valid until must be a valid date',
        ];
    }
}
