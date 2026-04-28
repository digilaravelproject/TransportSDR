<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'lead_number',
        'trip_route',
        'trip_date',
        'duration_days',
        'vehicle_type',
        'seating_capacity',
        'pickup_address',
        'points',
        'customer_name',
        'customer_contact',
        'total_amount',
        'advance_amount',
        'pending_amount',
        'status',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'points'    => 'array',
        'total_amount'   => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($lead) {
            $year = now()->format('Y');
            $count = self::whereYear('created_at', $year)->count() + 1;
            $lead->lead_number = 'LD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            if (empty($lead->status)) {
                $lead->status = 'pending';
            }
        });
    }
}
