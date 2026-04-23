<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'billing_cycle_days',
        'max_vehicles',
        'max_trips_per_month',
        'max_staff',
        'features',
        'status',
        'sort_order',
        'module_access',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'max_vehicles' => 'integer',
        'max_trips_per_month' => 'integer',
        'max_staff' => 'integer',
        'billing_cycle_days' => 'integer',
        'sort_order' => 'integer',
        'module_access' => 'string',
    ];
    /**
     * Get module names as array.
     */
    public function getModuleAccessArrayAttribute()
    {
        return $this->module_access ? array_map('trim', explode(',', $this->module_access)) : [];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('price', 'asc');
    }

    public function hasUnlimitedVehicles(): bool
    {
        return is_null($this->max_vehicles);
    }

    public function hasUnlimitedTrips(): bool
    {
        return is_null($this->max_trips_per_month);
    }

    public function hasUnlimitedStaff(): bool
    {
        return is_null($this->max_staff);
    }
}

