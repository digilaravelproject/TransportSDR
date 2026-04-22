<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'owner_name',
        'email',
        'phone',
        'gstin',
        'address',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
        return $this->is_active === true;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset("storage/{$this->logo_path}")
            : null;
    }
}
