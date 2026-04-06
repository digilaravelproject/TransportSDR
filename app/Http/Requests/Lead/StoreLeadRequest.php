<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enquiry_date'         => 'nullable|date',
            'trip_route'           => 'required|string|max:255',
            'trip_date'            => 'required|date',
            'return_date'          => 'nullable|date|after_or_equal:trip_date',
            'duration_days'        => 'required|integer|min:1',
            'vehicle_type'         => 'required|string|max:100',
            'seating_capacity'     => 'required|integer|min:1',
            'number_of_vehicles'   => 'required|integer|min:1',
            'pickup_address'       => 'required|string',
            'destination_points'   => 'required|array|min:1',
            'destination_points.*' => 'required|string|max:255',
            'customer_name'        => 'required|string|max:255',
            'customer_contact'     => 'required|string|max:15',
            'customer_email'       => 'nullable|email',
            'customer_id'          => 'nullable|exists:customers,id',
            'quoted_amount'        => 'nullable|numeric|min:0',
            'advance_amount'       => 'nullable|numeric|min:0',
            'is_gst'               => 'boolean',
            'gst_percent'          => 'nullable|numeric|min:0|max:28',
            'source'               => 'nullable|in:phone,website,whatsapp,email,walkin,reference,other',
            'notes'                => 'nullable|string',
            'followup_date'        => 'nullable|date',
            'assigned_to'          => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'trip_route.required'         => 'Trip route is required.',
            'trip_date.required'          => 'Trip date is required.',
            'customer_name.required'      => 'Customer name is required.',
            'customer_contact.required'   => 'Customer contact number is required.',
            'destination_points.required' => 'At least one destination is required.',
            'vehicle_type.required'       => 'Vehicle type is required.',
            'seating_capacity.required'   => 'Seating capacity is required.',
        ];
    }
}
