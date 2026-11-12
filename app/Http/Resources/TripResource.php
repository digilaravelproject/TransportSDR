<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\StaffResource;
use App\Models\Vehicle;
use App\Models\Staff;
use Illuminate\Support\Collection;

class TripResource extends JsonResource
{
    public function toArray($request): array
    {
        // Newer trips keep all assigned vehicle IDs in assigned_vehicles, while
        // older/single-vehicle trips may only have vehicle_id populated.
        $vehicleIds = $this->normalizedAssignedVehicleIds()
            ->when($this->vehicle_id, fn ($ids) => $ids->push($this->vehicle_id))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // Trips converted before the assignment fix may have lost vehicle_id,
        // but their source lead still contains the selected vehicle.
        if ($vehicleIds->isEmpty() && $this->lead_id && $this->lead?->vehicle_id) {
            $vehicleIds->push($this->lead->vehicle_id);
        }

        $vehicles = $vehicleIds->isEmpty()
            ? collect()
            : Vehicle::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant_id)
                ->whereIn('id', $vehicleIds)
                ->get()
                ->sortBy(fn ($vehicle) => $vehicleIds->search((int) $vehicle->id))
                ->values();

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
            
            'vehicle_type_details' => $this->vehicleTypeDetails,

            'number_of_vehicles' => $this->number_of_vehicles,
            'vehicle_id' => $this->vehicle_id ?: $vehicleIds->first(),
            'assigned_vehicle_ids' => $vehicleIds->all(),
            'vehicle' => $vehicles->isNotEmpty()
                ? (new VehicleResource($vehicles->first()))->resolve($request)
                : null,
            'vehicles' => VehicleResource::collection($vehicles)->resolve($request),

            
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
            'drivers' => $this->assigned_drivers ? StaffResource::collection(Staff::whereIn('id', $this->assigned_drivers)->get()) : null,
            // 'helper' => $this->helper ? [
            //     'id'    => $this->helper->id,
            //     'name'  => $this->helper->name,
            //     'phone' => $this->helper->phone,
            // ] : null,
            // 'km' => [
            //     'start' => $this->start_km,
            //     'end'   => $this->end_km,
            //     'total' => $this->total_km,
            //     'grade' => $this->km_grade,
            // ],
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

    private function normalizedAssignedVehicleIds(): Collection
    {
        $ids = $this->assigned_vehicles;

        // Support normal JSON arrays and legacy/double-encoded JSON values.
        for ($attempt = 0; $attempt < 2 && is_string($ids); $attempt++) {
            $decoded = json_decode($ids, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }
            $ids = $decoded;
        }

        return collect(is_array($ids) ? $ids : []);
    }
}
