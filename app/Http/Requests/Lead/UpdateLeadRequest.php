<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enquiry_date'         => 'nullable|date',
            'trip_route'           => 'sometimes|string|max:255',
            'trip_date'            => 'sometimes|date',
            'return_date'          => 'nullable|date|after_or_equal:trip_date',
            'duration_days'        => 'sometimes|integer|min:1',
            'vehicle_type'         => 'sometimes|string|max:100',
            'seating_capacity'     => 'sometimes|integer|min:1',
            'number_of_vehicles'   => 'sometimes|integer|min:1',
            'pickup_address'       => 'sometimes|string',
            'destination_points'   => 'sometimes|array|min:1',
            'destination_points.*' => 'string|max:255',
            'customer_name'        => 'sometimes|string|max:255',
            'customer_contact'     => 'sometimes|string|max:15',
            'customer_email'       => 'nullable|email',
            'customer_id'          => 'nullable|exists:customers,id',
            'quoted_amount'        => 'nullable|numeric|min:0',
            'advance_amount'       => 'nullable|numeric|min:0',
            'is_gst'               => 'boolean',
            'gst_percent'          => 'nullable|numeric|min:0|max:28',
            'source'               => 'nullable|in:phone,website,whatsapp,email,walkin,reference,other',
            'notes'                => 'nullable|string',
            'followup_date'        => 'nullable|date',
            'followup_notes'       => 'nullable|string',
            'assigned_to'          => 'nullable|exists:users,id',
        ];
    }
}
