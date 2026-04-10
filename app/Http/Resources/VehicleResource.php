<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'registration_number' => $this->registration_number,
            'type'                => $this->type,
            'seating_capacity'    => $this->seating_capacity,
            'make'                => $this->make,
            'model'               => $this->model,
            'fuel_type'           => $this->fuel_type,
            'current_km'          => $this->current_km,
            'is_available'        => $this->is_available,
            'is_active'           => $this->is_active,
            'trips_count'         => $this->trips_count ?? 0,
            'created_at'          => $this->created_at?->format('d-m-Y'),
        ];
    }
}
