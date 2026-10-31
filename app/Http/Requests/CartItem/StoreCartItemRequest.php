<?php

namespace App\Http\Requests\CartItem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('products', 'id'),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99999999',
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
            'product_id.required' => 'The product is required.',
            'product_id.string' => 'The product must be identified by a valid UUID.',
            'product_id.uuid' => 'The product must be identified by a valid UUID.',
            'product_id.exists' => 'The selected product does not exist.',

            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be a whole number.',
            'quantity.min' => 'The quantity must be at least 1.',
            'quantity.max' => 'The quantity may not be greater than :max.',
        ];
    }
}
