<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VehicleSparePart extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'part_name',
        'part_number',
        'category',
        'quantity_in_stock',
        'minimum_stock_alert',
        'unit',
        'unit_price',
        'total_value',
        'condition',
        'last_replaced_on',
        'km_at_replacement',
        'replacement_interval_km',
        'vendor_name',
        'is_available',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'last_replaced_on'         => 'date',
        'unit_price'               => 'decimal:2',
        'total_value'              => 'decimal:2',
        'km_at_replacement'        => 'decimal:2',
        'replacement_interval_km'  => 'decimal:2',
        'is_available'             => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
        static::saving(function ($part) {
            $part->total_value = round($part->quantity_in_stock * $part->unit_price, 2);
        });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function isLowStock(): bool
    {
        return $this->quantity_in_stock <= $this->minimum_stock_alert;
    }
}
