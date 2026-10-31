<?php

namespace App\Http\Requests\ProductImage;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductImageRequest extends FormRequest
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
            'product_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('products', 'id'),
            ],

            'image' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],

            'is_primary' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
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

            'image.required' => 'The image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a PNG, JPG, JPEG, or WEBP file.',
            'image.max' => 'The image may not be larger than 2MB.',

            'is_primary.required' => 'The primary image flag is required.',
            'is_primary.boolean' => 'The primary image flag must be true or false.',

            'sort_order.integer' => 'The sort order must be a whole number.',
            'sort_order.min' => 'The sort order cannot be negative.',
            'sort_order.max' => 'The sort order may not be greater than :max.',
        ];
    }
}
