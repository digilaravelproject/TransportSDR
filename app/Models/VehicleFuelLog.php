<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VehicleFuelLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'fuel_type',
        'quantity_liters',
        'price_per_liter',
        'total_cost',
        'km_at_fill',
        'km_since_last_fill',
        'fuel_efficiency',
        'fuel_station',
        'payment_mode',
        'bill_number',
        'bill_image',
        'filled_on',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'filled_on'        => 'date',
        'quantity_liters'  => 'decimal:2',
        'price_per_liter'  => 'decimal:2',
        'total_cost'       => 'decimal:2',
        'km_at_fill'       => 'decimal:2',
        'fuel_efficiency'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());

        static::saving(function ($log) {
            $log->total_cost = round($log->quantity_liters * $log->price_per_liter, 2);

            // Calculate efficiency from previous fill
            $prev = self::where('vehicle_id', $log->vehicle_id)
                ->where('id', '!=', $log->id ?? 0)
                ->latest()
                ->first();

            if ($prev) {
                $log->km_since_last_fill = $log->km_at_fill - $prev->km_at_fill;
                if ($log->km_since_last_fill > 0 && $log->quantity_liters > 0) {
                    $log->fuel_efficiency = round($log->km_since_last_fill / $log->quantity_liters, 2);
                }
            }
        });

        // Auto ledger entry
        static::created(function ($log) {
            VehicleLedger::create([
                'tenant_id'      => $log->tenant_id,
                'vehicle_id'     => $log->vehicle_id,
                'entry_type'     => 'expense',
                'category'       => 'fuel',
                'reference_type' => 'VehicleFuelLog',
                'reference_id'   => $log->id,
                'description'    => "Fuel ({$log->fuel_type}) - {$log->quantity_liters}L",
                'amount'         => $log->total_cost,
                'entry_date'     => $log->filled_on,
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
