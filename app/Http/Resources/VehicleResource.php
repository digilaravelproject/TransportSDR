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
            'model_year'          => $this->model_year,
            'per_km_price'        => $this->per_km_price,
            'ac_price_per_km'     => $this->ac_price_per_km,
            'rc_number'           => $this->rc_number,
            'rc_expiry'           => $this->rc_expiry?->format('d-m-Y'),
            'rc_file_url'         => $this->rc_file ? asset("storage/{$this->rc_file}") : null,
            'insurance_number'    => $this->insurance_number,
            'insurance_expiry'    => $this->insurance_expiry?->format('d-m-Y'),
            'insurance_file_url'  => $this->insurance_file ? asset("storage/{$this->insurance_file}") : null,
            'permit_number'       => $this->permit_number,
            'permit_expiry'       => $this->permit_expiry?->format('d-m-Y'),
            'permit_file_url'     => $this->permit_file ? asset("storage/{$this->permit_file}") : null,
            'is_available'        => $this->is_available,
            'is_active'           => $this->is_active,
            'trips_count'         => $this->trips_count ?? 0,
            'created_at'          => $this->created_at?->format('d-m-Y'),
        ];
    }
}
