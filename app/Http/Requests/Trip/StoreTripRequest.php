<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'trip_date'            => 'required|date',
            'return_date'          => 'nullable|date|after_or_equal:trip_date',
            'duration_days'        => 'required|integer|min:1',
            'trip_route'           => 'required|string|max:255',
            'pickup_address'       => 'required|string',
            'destination_points'   => 'required|array|min:1',
            'destination_points.*' => 'required|string|max:255',
            // vehicle_id is not provided on create API; assignment happens later
            'vehicle_type'         => 'required|integer|exists:vehicle_types,id',
            'seating_capacity'     => 'required|integer|min:1',
            'number_of_vehicles'   => 'required|integer|min:1',
            'customer_id'          => 'nullable|exists:customers,id',
            'customer_name'        => 'nullable|string|max:255',
            'customer_contact'     => 'nullable|string|max:15',
            'driver_id'            => 'nullable|exists:staff,id',
            'helper_id'            => 'nullable|exists:staff,id',
            'total_amount'         => 'required|numeric|min:0',
            'advance_amount'       => 'nullable|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',
            'is_gst'               => 'boolean',
            'gst_percent'          => 'nullable|numeric|min:0|max:28',
            'start_km'             => 'nullable|numeric|min:0',
            'end_km'               => 'nullable|numeric|min:0|gte:start_km',
            'notes'                => 'nullable|string',
        ];
    }
}
