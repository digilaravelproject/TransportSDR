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
        'staff_type',         // driver, helper, office
        'date_of_birth',
        'date_of_joining',
        'address',
        'emergency_contact',
        'emergency_contact_name',

        // License (for drivers)
        'license_number',
        'license_expiry',
        'license_type',

        // Salary config
        'basic_salary',
        'da_per_day',         // daily allowance per trip day
        'hra',
        'other_allowance',

        // Bank details
        'bank_name',
        'bank_account',
        'bank_ifsc',

        'is_available',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'date_of_joining'  => 'date',
        'license_expiry'   => 'date',
        'is_available'     => 'boolean',
        'is_active'        => 'boolean',
        'basic_salary'     => 'decimal:2',
        'da_per_day'       => 'decimal:2',
        'hra'              => 'decimal:2',
        'other_allowance'  => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function attendance()
    {
        return $this->hasMany(StaffAttendance::class);
    }
    public function salaries()
    {
        return $this->hasMany(StaffSalary::class);
    }
    public function advances()
    {
        return $this->hasMany(StaffAdvance::class);
    }
    public function daLogs()
    {
        return $this->hasMany(StaffDaLog::class);
    }
    public function documents()
    {
        return $this->hasMany(StaffDocument::class);
    }
    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }
    public function helperTrips()
    {
        return $this->hasMany(Trip::class, 'helper_id');
    }

    // Scopes
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

    // Pending advance amount
    public function pendingAdvanceAmount(): float
    {
        return (float) $this->advances()
            ->where('is_deducted', false)
            ->sum('amount');
    }

    /**
     * Shifts assigned to this driver
     */
    public function shifts()
    {
        return $this->belongsToMany(\App\Models\Shift::class, 'shift_driver', 'driver_id', 'shift_id');
    }
    public function role()
    {
        return $this->belongsTo(\App\Models\RoleModule::class, 'staff_type');
    }
}
