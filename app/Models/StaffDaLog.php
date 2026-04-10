<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class StaffDaLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'trip_id',
        'da_amount',
        'trip_days',
        'da_per_day',
        'extra_allowance',
        'notes',
        'status',
        'paid_on',
        'created_by',
    ];

    protected $casts = [
        'paid_on'        => 'date',
        'da_amount'      => 'decimal:2',
        'da_per_day'     => 'decimal:2',
        'extra_allowance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
        static::saving(function ($da) {
            $da->da_amount = ($da->da_per_day * $da->trip_days) + $da->extra_allowance;
        });
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
