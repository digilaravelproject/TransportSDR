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
        'make',
        'model',
        'fuel_type',
        'current_km',
        'is_available',
        'is_active',
    ];
    protected $casts = ['is_available' => 'boolean', 'is_active' => 'boolean'];
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
