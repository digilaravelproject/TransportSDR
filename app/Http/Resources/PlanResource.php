<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration' => $this->duration,
            'billing_cycle_days' => $this->billing_cycle_days,
            'max_vehicles' => $this->max_vehicles,
            'max_trips_per_month' => $this->max_trips_per_month,
            'max_staff' => $this->max_staff,
            'features' => $this->features ?? [],
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'has_unlimited_vehicles' => $this->hasUnlimitedVehicles(),
            'has_unlimited_trips' => $this->hasUnlimitedTrips(),
            'has_unlimited_staff' => $this->hasUnlimitedStaff(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
