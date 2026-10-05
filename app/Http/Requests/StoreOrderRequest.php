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
            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | ORDER ITEMS
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER FILES
            |--------------------------------------------------------------------------
            |
            | Customer dapat mengirim beberapa foto sekaligus.
            |
            | Maksimal:
            | - 10 file
            | - 5 MB per file
            |
            */

            'files' => [
                'nullable',
                'array',
                'max:10',
            ],

            'files.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

            'customer.name.required' => 'Nama customer wajib diisi.',

            'customer.email.email' => 'Format email tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            'items.required' => 'Minimal satu service harus dipilih.',

            'items.min' => 'Minimal satu service harus dipilih.',

            'items.*.service_id.exists' => 'Service tidak ditemukan.',

            'items.*.quantity.min' => 'Quantity minimal 1.',

            'items.*.quantity.max' => 'Quantity maksimal 100.',

            /*
            |--------------------------------------------------------------------------
            | FILES
            |--------------------------------------------------------------------------
            */

            'files.array' => 'Format file tidak valid.',

            'files.max' => 'Maksimal 10 foto dapat diupload.',

            'files.*.file' => 'File yang diupload tidak valid.',

            'files.*.image' => 'File harus berupa gambar.',

            'files.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',

            'files.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }
}
