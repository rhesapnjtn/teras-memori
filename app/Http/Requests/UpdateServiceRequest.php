<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')->id;

        return [
            'name' => [
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
                Rule::unique('services', 'slug')->ignore($serviceId),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'duration' => [
                'nullable',
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}