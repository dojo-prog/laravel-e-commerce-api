<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasRole(['admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:4',
                'max:30',
                Rule::unique('coupons', 'code'),
            ],

            'type' => [
                'required',
                'string',
                'in:fixed,percentage',
            ],

            'value' => [
                'bail',
                'required',
                'decimal:0,2',
                'min:0',
                'max:99999999',
            ],

            'maximum_order_amount' => [
                'required',
                'integer',
                'min:0',
                'max:99999999',
            ],

            'maximum_discount' => [
                'required',
                'decimal:0,2',
                'min:0',
                'max:99999999',
            ],

            'usage_limit' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:99999999',
            ],

            'starts_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'expires_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The coupon code is required.',
            'code.string' => 'The coupon code must be a string.',
            'code.min' => 'The coupon code must be at least :min characters.',
            'code.max' => 'The coupon code may not be greater than :max characters.',
            'code.unique' => 'This coupon code has already been taken.',

            'type.required' => 'The coupon type is required.',
            'type.string' => 'The coupon type must be a string.',
            'type.in' => 'The coupon type must be either fixed or percentage.',

            'value.required' => 'The discount value is required.',
            'value.decimal' => 'The discount value must be a valid number.',
            'value.min' => 'The discount value must be at least :min.',
            'value.max' => 'The discount value may not be greater than :max.',

            'maximum_order_amount.required' => 'The maximum order amount is required.',
            'maximum_order_amount.integer' => 'The maximum order amount must be a whole number.',
            'maximum_order_amount.min' => 'The maximum order amount cannot be negative.',
            'maximum_order_amount.max' => 'The maximum order amount may not be greater than :max.',

            'maximum_discount.required' => 'The maximum discount is required.',
            'maximum_discount.decimal' => 'The maximum discount must be a valid number.',
            'maximum_discount.min' => 'The maximum discount must be at least :min.',
            'maximum_discount.max' => 'The maximum discount may not be greater than :max.',

            'usage_limit.integer' => 'The usage limit must be a whole number.',
            'usage_limit.min' => 'The usage limit must be at least 1.',
            'usage_limit.max' => 'The usage limit may not be greater than :max.',

            'starts_at.date' => 'The start date must be a valid date.',

            'expires_at.date' => 'The expiry date must be a valid date.',

            'is_active.required' => 'The active status is required.',
            'is_active.boolean' => 'The active status field must be true or false.',
        ];
    }
}
