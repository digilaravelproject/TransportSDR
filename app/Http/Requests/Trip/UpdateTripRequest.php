<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'trip_date'            => 'sometimes|date',
            'return_date'          => 'nullable|date|after_or_equal:trip_date',
            'duration_days'        => 'sometimes|integer|min:1',
            'trip_route'           => 'sometimes|string|max:255',
            'pickup_address'       => 'sometimes|string',
            'destination_points'   => 'sometimes|array|min:1',
            'destination_points.*' => 'string|max:255',
            'vehicle_id'           => 'sometimes|exists:vehicles,id',
            'vehicle_type'         => 'sometimes|string|max:100',
            'seating_capacity'     => 'sometimes|integer|min:1',
            'number_of_vehicles'   => 'sometimes|integer|min:1',
            'customer_id'          => 'sometimes|exists:customers,id',
            'customer_name'        => 'sometimes|string|max:255',
            'customer_contact'     => 'sometimes|string|max:15',
            'driver_id'            => 'nullable|exists:staff,id',
            'helper_id'            => 'nullable|exists:staff,id',
            'total_amount'         => 'sometimes|numeric|min:0',
            'advance_amount'       => 'nullable|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',
            'is_gst'               => 'boolean',
            'gst_percent'          => 'nullable|numeric|min:0|max:28',
            'start_km'             => 'nullable|numeric|min:0',
            'end_km'               => 'nullable|numeric|min:0|gte:start_km',
            'status'               => 'sometimes|in:scheduled,ongoing,completed,cancelled',
            'notes'                => 'nullable|string',
        ];
    }
}
