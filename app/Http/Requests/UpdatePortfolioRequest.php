<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $portfolioId = $this->route('portfolio')->id;

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('portfolios', 'slug')->ignore($portfolioId),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_published' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}