<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceRequest extends FormRequest
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
            'preferred_sources' => ['nullable', 'array'],
            'preferred_sources.*' => ['string', 'max:100'],
            'preferred_categories' => ['nullable', 'array'],
            'preferred_categories.*' => ['integer', 'exists:categories,id'],
            'preferred_authors' => ['nullable', 'array'],
            'preferred_authors.*' => ['integer', 'exists:authors,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferred_sources.array' => 'Preferred sources must be an array.',
            'preferred_sources.*.string' => 'Each preferred source must be a string.',
            'preferred_sources.*.max' => 'Each preferred source must not exceed 100 characters.',

            'preferred_categories.array' => 'Preferred categories must be an array.',
            'preferred_categories.*.integer' => 'Each preferred category must be a valid ID.',
            'preferred_categories.*.exists' => 'The selected category does not exist.',

            'preferred_authors.array' => 'Preferred authors must be an array.',
            'preferred_authors.*.integer' => 'Each preferred author must be a valid ID.',
            'preferred_authors.*.exists' => 'The selected author does not exist.',
        ];
    }
}
