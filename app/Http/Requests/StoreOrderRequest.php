<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer.name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer.email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'customer.phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'customer.address' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer.name.required' => 'Nama customer wajib diisi.',
            'customer.email.email' => 'Format email tidak valid.',
            'items.required' => 'Minimal satu service harus dipilih.',
            'items.min' => 'Minimal satu service harus dipilih.',
            'items.*.service_id.exists' => 'Service tidak ditemukan.',
            'items.*.quantity.min' => 'Quantity minimal 1.',
            'items.*.quantity.max' => 'Quantity maksimal 100.',
        ];
    }
}