<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories')
                    ->where(function ($query) {
                        return $query->whereRaw(
                            'LOWER(name) = ?',
                            [strtolower($this->name)]
                        );
                    })
                    ->ignore($this->route('category')),
            ],

            'description' => ['sometimes', 'nullable', 'string', 'min:10', 'max:255'],

            'image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
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
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.min' => 'The category name must be at least :min characters.',
            'name.max' => 'The category name may not be greater than :max characters.',
            'name.unique' => 'This category name is already in use.',

            'description.string' => 'The description must be a string.',
            'description.min' => 'The description must be at least :min characters.',
            'description.max' => 'The description may not be greater than :max characters.',

            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a PNG, JPG, JPEG, or WEBP file.',
            'image.max' => 'The image may not be larger than 2MB.',
        ];
    }
}
