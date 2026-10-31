<?php

namespace App\Http\Requests\Address;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'recipient_name' => [
                'required',
                'string',
                'min:4',
                'max:100',
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'digits:11',
                'regex:/^09[0-9]{9}$/',
            ],

            'address_line_1' => [
                'required',
                'string',
                'min:10',
                'max:360',
            ],

            'address_line_2' => [
                'sometimes',
                'nullable',
                'string',
                'min:10',
                'max:360',
            ],

            'city' => [
                'required',
                'string',
                'min:4',
                'max:100',
            ],

            'province' => [
                'required',
                'string',
                'min:4',
                'max:100',
            ],

            'postal_code' => [
                'required',
                'string',
                'digits:4',
            ],

            'country' => [
                'sometimes',
                'string',
                'min:4',
                'max:100',
            ],

            'is_default' => [
                'sometimes',
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
            'recipient_name.required' => 'The recipient name is required.',
            'recipient_name.string' => 'The recipient name must be a string.',
            'recipient_name.min' => 'The recipient name must be at least :min characters.',
            'recipient_name.max' => 'The recipient name may not be greater than :max characters.',

            'phone.string' => 'The phone number must be a string.',
            'phone.digits' => 'The phone number must be :digits digits.',
            'phone.regex' => 'The phone number must be a valid mobile number starting with 09.',

            'address_line_1.required' => 'The first address line is required.',
            'address_line_1.string' => 'The first address line must be a string.',
            'address_line_1.min' => 'The first address line must be at least :min characters.',
            'address_line_1.max' => 'The first address line may not be greater than :max characters.',

            'address_line_2.string' => 'The second address line must be a string.',
            'address_line_2.min' => 'The second address line must be at least :min characters.',
            'address_line_2.max' => 'The second address line may not be greater than :max characters.',

            'city.required' => 'The city is required.',
            'city.string' => 'The city must be a string.',
            'city.min' => 'The city must be at least :min characters.',
            'city.max' => 'The city may not be greater than :max characters.',

            'province.required' => 'The province is required.',
            'province.string' => 'The province must be a string.',
            'province.min' => 'The province must be at least :min characters.',
            'province.max' => 'The province may not be greater than :max characters.',

            'postal_code.required' => 'The postal code is required.',
            'postal_code.string' => 'The postal code must be a string.',
            'postal_code.digits' => 'The postal code must be exactly :digits digits.',

            'country.string' => 'The country must be a string.',
            'country.min' => 'The country must be at least :min characters.',
            'country.max' => 'The country may not be greater than :max characters.',

            'is_default.boolean' => 'The default status field must be true or false.',
        ];
    }
}
