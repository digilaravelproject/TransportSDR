<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'lead_number'         => $this->lead_number,
            'enquiry_date'        => $this->enquiry_date?->format('d-m-Y'),
            'trip_date'           => $this->trip_date?->format('d-m-Y'),
            'return_date'         => $this->return_date?->format('d-m-Y'),
            'duration_days'       => $this->duration_days,
            'trip_route'          => $this->trip_route,
            'pickup_address'      => $this->pickup_address,
            'destination_points'  => $this->destination_points,
            'status'              => $this->status,
            'source'              => $this->source,

            'vehicle' => [
                'type'               => $this->vehicle_type,
                'seating_capacity'   => $this->seating_capacity,
                'number_of_vehicles' => $this->number_of_vehicles,
            ],

            'customer' => [
                'id'      => $this->customer_id,
                'name'    => $this->customer_name,
                'contact' => $this->customer_contact,
                'email'   => $this->customer_email,
            ],

            'quotation' => [
                'quoted_amount'  => (float) $this->quoted_amount,
                'advance_amount' => (float) $this->advance_amount,
                'is_gst'         => $this->is_gst,
                'gst_percent'    => (float) $this->gst_percent,
            ],

            'followup' => [
                'followup_date'  => $this->followup_date?->format('d-m-Y'),
                'followup_notes' => $this->followup_notes,
            ],

            'notes' => $this->notes,

            'converted' => [
                'is_converted'      => $this->isConverted(),
                'converted_at'      => $this->converted_at?->format('d-m-Y H:i'),
                'converted_trip_id' => $this->converted_trip_id,
                'trip_number'       => $this->convertedTrip?->trip_number,
            ],

            'assigned_to' => $this->assignedTo ? [
                'id'   => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
            ] : null,

            'created_by' => $this->creator ? [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ] : null,

            'created_at' => $this->created_at?->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i'),
        ];
    }
}
