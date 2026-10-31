<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'category_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('categories', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
                Rule::unique('products')
                    ->where(function ($query) {
                        return $query
                            ->rawWhere(
                                'LOWER(name) = ?',
                                [strtolower($this->name)]
                            )
                            ->where('category_id', $this->category_id);
                    }),
            ],

            'description' => [
                'required',
                'string',
                'min:10',
                'max:360',
            ],

            'price' => [
                'bail',
                'required',
                'decimal:0,2',
                'min:0',
                'max:99999999',
            ],

            'stock_quantity' => [
                'bail',
                'required',
                'integer',
                'min:0',
                'max:99999999',
            ],

            'status' => [
                'sometimes',
                'string',
                'in:draft,active,inactive',
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
            'category_id.required' => 'The category is required.',
            'category_id.string' => 'The category must be identified by a valid UUID.',
            'category_id.uuid' => 'The category must be identified by a valid UUID.',
            'category_id.exists' => 'The selected category does not exist.',

            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a string.',
            'name.min' => 'The product name must be at least :min characters.',
            'name.max' => 'The product name may not be greater than :max characters.',
            'name.unique' => 'A product with this name already exists in this category.',

            'description.required' => 'The product description is required.',
            'description.string' => 'The product description must be a string.',
            'description.min' => 'The product description must be at least :min characters.',
            'description.max' => 'The product description may not be greater than :max characters.',

            'price.required' => 'The price is required.',
            'price.decimal' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'price.max' => 'The price may not be greater than :max.',

            'stock_quantity.required' => 'The stock quantity is required.',
            'stock_quantity.integer' => 'The stock quantity must be a whole number.',
            'stock_quantity.min' => 'The stock quantity cannot be negative.',
            'stock_quantity.max' => 'The stock quantity may not be greater than :max.',

            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be draft, active, or inactive.',
        ];
    }
}
