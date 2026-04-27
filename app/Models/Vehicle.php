<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Vehicle extends Model
{
    use SoftDeletes, BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'registration_number',
        'type',
        'seating_capacity',
        'model_year',
        'per_km_price',
        'ac_price_per_km',
        'rc_number',
        'rc_expiry',
        'rc_file',
        'insurance_number',
        'insurance_expiry',
        'insurance_file',
        'permit_number',
        'permit_expiry',
        'permit_file',
        'is_available',
        'is_active',
    ];
    protected $casts = [
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'per_km_price' => 'decimal:2',
        'ac_price_per_km' => 'decimal:2',
        'rc_expiry' => 'date',
        'insurance_expiry' => 'date',
        'permit_expiry' => 'date',
    ];
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
    public function scopeAvailable($q)
    {
        return $q->where('is_available', true)->where('is_active', true);
    }
    public function maintenanceLogs()
    {
        return $this->hasMany(VehicleMaintenanceLog::class);
    }
}
