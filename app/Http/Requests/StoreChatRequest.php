<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'order_number' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',

            'email.email' => 'Format email tidak valid.',

            'email.max' => 'Email maksimal 255 karakter.',

            'order_number.required' => 'Nomor order wajib diisi.',

            'order_number.max' => 'Nomor order maksimal 100 karakter.',
        ];
    }
}
