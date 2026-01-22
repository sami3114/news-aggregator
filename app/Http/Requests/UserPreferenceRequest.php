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
            'preferred_categories.*' => ['string', 'max:100'],
            'preferred_authors' => ['nullable', 'array'],
            'preferred_authors.*' => ['string', 'max:255'],
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
            'preferred_categories.array' => 'Preferred categories must be an array.',
            'preferred_authors.array' => 'Preferred authors must be an array.',
        ];
    }
}
