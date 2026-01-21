<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleFilterRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:255'],
            'from_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'from_date.date_format' => 'The from date must be in Y-m-d format.',
            'to_date.date_format' => 'The to date must be in Y-m-d format.',
            'to_date.after_or_equal' => 'The to date must be after or equal to the from date.',
            'per_page.max' => 'Maximum items per page is 100.',
        ];
    }
}
