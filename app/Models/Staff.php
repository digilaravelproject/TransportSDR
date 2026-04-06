<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Staff extends Model
{
    use SoftDeletes, BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'phone',
        'email',
        'staff_type',
        'license_number',
        'license_expiry',
        'is_available',
        'is_active',
    ];
    protected $casts = [
        'is_available'  => 'boolean',
        'is_active'     => 'boolean',
        'license_expiry' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }
    public function scopeDrivers($q)
    {
        return $q->where('staff_type', 'driver');
    }
    public function scopeHelpers($q)
    {
        return $q->where('staff_type', 'helper');
    }
    public function scopeAvailable($q)
    {
        return $q->where('is_available', true)->where('is_active', true);
    }
}
