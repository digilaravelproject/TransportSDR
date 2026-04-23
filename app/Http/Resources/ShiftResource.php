<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'formatted_time_range' => $this->time_range,
            'type' => $this->type,
            // 'duration_hours' => $this->calculateDuration(),
            'days' => $this->days ?? [],
            // 'day_names' => $this->getDayNames(),
            'is_active' => (bool) $this->is_active,
            'max_drivers' => $this->max_drivers,
            'hourly_rate' => $this->hourly_rate ? (float) $this->hourly_rate : null,
            'notes' => $this->notes,
            'drivers_count' => $this->drivers_count ?? 0,
            'drivers' => StaffResource::collection($this->whenLoaded('drivers')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
