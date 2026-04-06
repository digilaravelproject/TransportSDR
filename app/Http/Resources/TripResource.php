<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'trip_number'  => $this->trip_number,
            'trip_date'    => $this->trip_date?->format('d-m-Y'),
            'return_date'  => $this->return_date?->format('d-m-Y'),
            'duration_days' => $this->duration_days,
            'trip_route'   => $this->trip_route,
            'pickup_address'     => $this->pickup_address,
            'destination_points' => $this->destination_points,
            'status'       => $this->status,

            'vehicle' => [
                'id'                 => $this->vehicle?->id,
                'registration'       => $this->vehicle?->registration_number,
                'type'               => $this->vehicle_type,
                'seating_capacity'   => $this->seating_capacity,
                'number_of_vehicles' => $this->number_of_vehicles,
            ],
            'customer' => [
                'id'      => $this->customer?->id,
                'name'    => $this->customer_name,
                'contact' => $this->customer_contact,
            ],
            'driver' => $this->driver ? [
                'id'    => $this->driver->id,
                'name'  => $this->driver->name,
                'phone' => $this->driver->phone,
            ] : null,
            'helper' => $this->helper ? [
                'id'    => $this->helper->id,
                'name'  => $this->helper->name,
                'phone' => $this->helper->phone,
            ] : null,
            'km' => [
                'start' => $this->start_km,
                'end'   => $this->end_km,
                'total' => $this->total_km,
                'grade' => $this->km_grade,
            ],
            'payment' => [
                'total'          => (float) $this->total_amount,
                'advance'        => (float) $this->advance_amount,
                'part_payment'   => (float) $this->part_payment,
                'discount'       => (float) $this->discount,
                'tax_amount'     => (float) $this->tax_amount,
                'gst_percent'    => (float) $this->gst_percent,
                'is_gst'         => $this->is_gst,
                'balance'        => (float) $this->balance_amount,
                'payment_status' => $this->payment_status,
            ],
            'payments'      => PaymentResource::collection($this->whenLoaded('payments')),
            'invoice_url'   => $this->invoice_path ? asset('storage/' . $this->invoice_path) : null,
            'duty_slip_url' => $this->duty_slip_path ? asset('storage/' . $this->duty_slip_path) : null,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->format('d-m-Y H:i'),
        ];
    }
}
