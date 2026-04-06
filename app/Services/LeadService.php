<?php

namespace App\Services;

use App\Models\{Lead, Trip, Vehicle, Staff, Customer};
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class LeadService
{
    public function store(array $data): Lead
    {
        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);
        return $lead->fresh(['customer', 'convertedTrip', 'assignedTo', 'creator']);
    }

    public function updateStatus(Lead $lead, array $data): Lead
    {
        // Cannot change status of already converted lead
        if ($lead->isConverted()) {
            abort(422, 'This lead has already been converted to a trip. Status cannot be changed.');
        }

        $lead->update([
            'status'         => $data['status'],
            'followup_date'  => $data['followup_date']  ?? $lead->followup_date,
            'followup_notes' => $data['followup_notes'] ?? $lead->followup_notes,
            'notes'          => $data['notes']          ?? $lead->notes,
        ]);

        return $lead->fresh();
    }

    public function convertToTrip(Lead $lead, array $data): Trip
    {
        if ($lead->isConverted()) {
            abort(422, 'This lead has already been converted to a trip.');
        }

        if ($lead->isLost()) {
            abort(422, 'Cannot convert a lost or cancelled lead to a trip.');
        }

        return DB::transaction(function () use ($lead, $data) {

            // Create trip from lead data
            $trip = Trip::create([
                'tenant_id'          => $lead->tenant_id,
                'trip_date'          => $lead->trip_date,
                'return_date'        => $lead->return_date,
                'duration_days'      => $lead->duration_days,
                'trip_route'         => $lead->trip_route,
                'pickup_address'     => $lead->pickup_address,
                'destination_points' => $lead->destination_points,
                'vehicle_id'         => $data['vehicle_id'],
                'vehicle_type'       => $lead->vehicle_type,
                'seating_capacity'   => $lead->seating_capacity,
                'number_of_vehicles' => $lead->number_of_vehicles,
                'customer_id'        => $data['customer_id'],
                'customer_name'      => $lead->customer_name,
                'customer_contact'   => $lead->customer_contact,
                'driver_id'          => $data['driver_id']      ?? null,
                'helper_id'          => $data['helper_id']      ?? null,
                'total_amount'       => $data['total_amount'],
                'advance_amount'     => $data['advance_amount'] ?? 0,
                'discount'           => $data['discount']       ?? 0,
                'is_gst'             => $data['is_gst']         ?? $lead->is_gst,
                'gst_percent'        => $data['gst_percent']    ?? $lead->gst_percent,
                'notes'              => $data['notes']          ?? $lead->notes,
                'status'             => 'scheduled',
            ]);

            // Lock vehicle & staff
            Vehicle::find($data['vehicle_id'])?->update(['is_available' => false]);
            if (!empty($data['driver_id'])) {
                Staff::find($data['driver_id'])?->update(['is_available' => false]);
            }
            if (!empty($data['helper_id'])) {
                Staff::find($data['helper_id'])?->update(['is_available' => false]);
            }

            // Update lead as converted
            $lead->update([
                'status'            => 'converted',
                'converted_trip_id' => $trip->id,
                'converted_at'      => now(),
            ]);

            return $trip->load(['vehicle', 'customer', 'driver', 'helper']);
        });
    }

    public function generateQuotation(Lead $lead): string
    {
        $lead->loadMissing(['tenant', 'customer']);

        $absoluteDir = storage_path(
            'app' . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR .
                $lead->tenant_id . DIRECTORY_SEPARATOR .
                'quotations'
        );

        $fileName     = "quotation-{$lead->id}.pdf";
        $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0775, true);
        }

        Pdf::loadView('pdf.lead-quotation', [
            'lead'   => $lead,
            'tenant' => $lead->tenant,
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        $storagePath = "tenants/{$lead->tenant_id}/quotations/{$fileName}";
        $lead->update([
            'quotation_path'     => $storagePath,
            'quotation_sent_at'  => now(),
            'status'             => $lead->status === 'new' ? 'quoted' : $lead->status,
        ]);

        return $absoluteFile;
    }

    public function generateBill(Lead $lead): string
    {
        $lead->loadMissing(['tenant', 'customer']);

        $absoluteDir = storage_path(
            'app' . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR .
                $lead->tenant_id . DIRECTORY_SEPARATOR .
                'lead-bills'
        );

        $fileName     = "bill-{$lead->id}.pdf";
        $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0775, true);
        }

        Pdf::loadView('pdf.lead-bill', [
            'lead'   => $lead,
            'tenant' => $lead->tenant,
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        $storagePath = "tenants/{$lead->tenant_id}/lead-bills/{$fileName}";
        $lead->update(['bill_path' => $storagePath]);

        return $absoluteFile;
    }
}
