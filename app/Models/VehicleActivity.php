<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleActivity extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_activities';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'activity_type',
        'title',
        'activity_date',
        'amount',
        'quantity',
        'price_per_unit',
        'station_name',
        'workshop_name',
        'garage_name',
        'receipt_path',
        'meta',
        'created_by'
    ];

    protected $casts = [
        'activity_date' => 'date',
        'meta' => 'array',
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
    ];
}