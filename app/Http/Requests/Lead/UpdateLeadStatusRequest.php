<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'         => 'required|in:new,contacted,followup,quoted,confirmed,converted,lost,cancelled',
            'followup_date'  => 'nullable|date',
            'followup_notes' => 'nullable|string',
            'notes'          => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in'       => 'Invalid status value.',
        ];
    }
}
