<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class AddPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'amount'       => 'required|numeric|min:1',
            'type'         => 'required|in:advance,part,final',
            'mode'         => 'required|in:cash,online,cheque,upi',
            'reference'    => 'nullable|string|max:100',
            'paid_on'      => 'required|date',
            'collected_by' => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
        ];
    }
}
