<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'     => 'required|exists:vehicles,id',
            'driver_id'      => 'nullable|exists:staff,id',
            'helper_id'      => 'nullable|exists:staff,id',
            'customer_id'    => 'required|exists:customers,id',
            'total_amount'   => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'is_gst'         => 'boolean',
            'gst_percent'    => 'nullable|numeric|min:0|max:28',
            'notes'          => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required'   => 'Please select a vehicle.',
            'vehicle_id.exists'     => 'Selected vehicle does not exist.',
            'customer_id.required'  => 'Please select a customer.',
            'total_amount.required' => 'Total amount is required.',
        ];
    }
}
