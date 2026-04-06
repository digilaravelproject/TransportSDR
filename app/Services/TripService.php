<?php

namespace App\Services;

use App\Models\{Trip, TripPayment, Vehicle, Staff};
use Illuminate\Support\Facades\{DB, Storage};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class TripService
{
    public function store(array $data): Trip
    {
        return DB::transaction(function () use ($data) {
            $trip = Trip::create($data);
            $this->lockResources(
                $data['vehicle_id'],
                $data['driver_id'] ?? null,
                $data['helper_id'] ?? null
            );
            return $trip->load(['vehicle', 'customer', 'driver', 'helper']);
        });
    }

    public function update(Trip $trip, array $data): Trip
    {
        return DB::transaction(function () use ($trip, $data) {
            if (isset($data['vehicle_id']) && $data['vehicle_id'] != $trip->vehicle_id) {
                Vehicle::find($trip->vehicle_id)?->update(['is_available' => true]);
                Vehicle::find($data['vehicle_id'])?->update(['is_available' => false]);
            }

            if (array_key_exists('driver_id', $data) && $data['driver_id'] != $trip->driver_id) {
                Staff::find($trip->driver_id)?->update(['is_available' => true]);
                if ($data['driver_id']) {
                    Staff::find($data['driver_id'])?->update(['is_available' => false]);
                }
            }

            $trip->update($data);
            return $trip->fresh(['vehicle', 'customer', 'driver', 'helper', 'payments']);
        });
    }

    public function addPayment(Trip $trip, array $data): TripPayment
    {
        return DB::transaction(function () use ($trip, $data) {
            $payment = $trip->payments()->create(
                array_merge($data, ['tenant_id' => $trip->tenant_id])
            );

            $advance = $trip->payments()->where('type', 'advance')->sum('amount');
            $parts   = $trip->payments()->whereIn('type', ['part', 'final'])->sum('amount');
            $trip->update(['advance_amount' => $advance, 'part_payment' => $parts]);

            return $payment;
        });
    }

    public function complete(Trip $trip): Trip
    {
        abort_if($trip->status === 'completed', 422, 'Trip already completed.');

        DB::transaction(function () use ($trip) {
            $trip->update(['status' => 'completed']);
            $this->releaseResources($trip);
            if ($trip->end_km) {
                Vehicle::find($trip->vehicle_id)?->update(['current_km' => $trip->end_km]);
            }
        });

        return $trip->fresh();
    }

    public function generateInvoice(Trip $trip): string
    {
        // Sabhi relations load karo
        $trip->loadMissing(['tenant', 'vehicle', 'customer', 'driver', 'helper', 'payments']);

        // Windows + Linux dono ke liye safe absolute path
        $absoluteDir = storage_path(
            'app' . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR .
                $trip->tenant_id . DIRECTORY_SEPARATOR .
                'invoices'
        );

        $fileName     = "trip-{$trip->id}.pdf";
        $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        // Folder exist nahi karta to banao
        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0775, true);
        }

        // PDF generate karo aur seedha save karo
        Pdf::loadView('pdf.trip-invoice', [
            'trip'   => $trip,
            'tenant' => $trip->tenant,
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        // DB mein forward slash wala path store karo (URL ke liye)
        $storagePath = "tenants/{$trip->tenant_id}/invoices/{$fileName}";
        $trip->update(['invoice_path' => $storagePath]);

        // Absolute path return karo controller ke liye
        return $absoluteFile;
    }

    public function generateDutySlip(Trip $trip): string
    {
        $trip->loadMissing(['tenant', 'vehicle', 'customer', 'driver', 'helper']);

        $absoluteDir = storage_path(
            'app' . DIRECTORY_SEPARATOR .
                'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR .
                $trip->tenant_id . DIRECTORY_SEPARATOR .
                'duty-slips'
        );

        $fileName     = "trip-{$trip->id}.pdf";
        $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0775, true);
        }

        Pdf::loadView('pdf.duty-slip', [
            'trip'   => $trip,
            'tenant' => $trip->tenant,
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        $storagePath = "tenants/{$trip->tenant_id}/duty-slips/{$fileName}";
        $trip->update(['duty_slip_path' => $storagePath]);

        return $absoluteFile;
    }

    private function lockResources($vehicleId, $driverId, $helperId): void
    {
        Vehicle::find($vehicleId)?->update(['is_available' => false]);
        if ($driverId) Staff::find($driverId)?->update(['is_available' => false]);
        if ($helperId) Staff::find($helperId)?->update(['is_available' => false]);
    }

    private function releaseResources(Trip $trip): void
    {
        Vehicle::find($trip->vehicle_id)?->update(['is_available' => true]);
        Staff::find($trip->driver_id)?->update(['is_available' => true]);
        Staff::find($trip->helper_id)?->update(['is_available' => true]);
    }
}
