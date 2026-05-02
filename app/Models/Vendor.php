<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Vendor extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vendor_name',
        'contact_number',
        'start_date',
        'end_date',
        'duty_type',
        'vehicle_type',
        'quantity',
        'monthly_amount',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'monthly_amount' => 'decimal:2',
    ];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vendor_vehicle')->withTimestamps();
    }

    public function bills()
    {
        return $this->hasMany(VendorBill::class);
    }
}
