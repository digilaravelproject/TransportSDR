<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VehicleMaintenanceLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'maintenance_type',
        'title',
        'description',
        'labour_cost',
        'parts_cost',
        'total_cost',
        'km_at_service',
        'next_service_km',
        'next_service_date',
        'vendor_name',
        'vendor_contact',
        'bill_number',
        'bill_image',
        'status',
        'service_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'service_date'     => 'date',
        'next_service_date' => 'date',
        'labour_cost'      => 'decimal:2',
        'parts_cost'       => 'decimal:2',
        'total_cost'       => 'decimal:2',
        'km_at_service'    => 'decimal:2',
        'next_service_km'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());

        static::saving(function ($log) {
            $log->total_cost = $log->labour_cost + $log->parts_cost;
        });

        // Auto ledger entry
        static::created(function ($log) {
            VehicleLedger::create([
                'tenant_id'      => $log->tenant_id,
                'vehicle_id'     => $log->vehicle_id,
                'entry_type'     => 'expense',
                'category'       => $log->maintenance_type === 'repair' ? 'repair' : 'maintenance',
                'reference_type' => 'VehicleMaintenanceLog',
                'reference_id'   => $log->id,
                'description'    => "{$log->title} ({$log->maintenance_type})",
                'amount'         => $log->total_cost,
                'entry_date'     => $log->service_date,
                'created_by'     => $log->created_by,
            ]);
        });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
