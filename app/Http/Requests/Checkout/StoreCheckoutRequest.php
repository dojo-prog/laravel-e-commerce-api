<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
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
            'address_id' => [
                'required',
                'uuid',
                Rule::exists('addresses', 'id')
                    ->where('user_id', $this->user()->id),
            ],
            'coupon_code' => ['sometimes', 'nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', Rule::in(['mock'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }
}
