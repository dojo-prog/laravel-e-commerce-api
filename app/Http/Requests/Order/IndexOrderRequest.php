<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexOrderRequest extends FormRequest
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
            'page' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer'],
            'status' => [
                'sometimes',
                'string',
                'in:pending,confirmed,processing,shipped,delivered,cancelled',
            ],
            'payment_status' => [
                'sometimes',
                'string',
                'in:pending,paid,failed,refunded',
            ],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'order_number' => ['sometimes', 'string'],
            'sort_by' => ['sometimes', 'string', 'in:created_at'],
            'sort_order' => ['sometimes', 'string', 'in:desc,asc'],
        ];
    }
}
