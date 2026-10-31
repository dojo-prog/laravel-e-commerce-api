<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category_id' => ['sometimes', 'string', 'uuid'],
            'min_price' => ['sometimes', 'decimal:0,2', 'min:0', 'max:999999999'],
            'max_price' => ['sometimes', 'decimal:0,2', 'min:0', 'max:999999999'],
            'status' => ['sometimes', 'string', 'in:draft,inactive,active'],
            'in_stock' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', 'in:created_at,name'],
            'sort_order' => ['sometimes', 'string', 'in:desc,asc'],
        ];
    }
}
