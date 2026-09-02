<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'paid',
                    'failed',
                    'expired',
                    'refunded',
                ]),
            ],

            'method' => [
                'nullable',
                Rule::in([
                    'bank_transfer',
                    'e_wallet',
                    'qris',
                    'other',
                ]),
            ],

            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}