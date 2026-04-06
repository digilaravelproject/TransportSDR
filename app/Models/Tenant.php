<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'gstin',
        'address',
        'logo_path',
        'plan',
        'max_vehicles',
        'max_trips_per_month',
        'is_active',
        'plan_expires_at',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'plan_expires_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function isActive(): bool
    {
        return $this->is_active &&
            ($this->plan_expires_at === null || $this->plan_expires_at->isFuture());
    }
}
