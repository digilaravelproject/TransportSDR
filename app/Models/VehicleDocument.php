<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VehicleDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'document_type',
        'document_number',
        'document_path',
        'issue_date',
        'expiry_date',
        'alert_before_days',
        'is_expired',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'is_expired'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());

        static::saving(function ($doc) {
            $doc->is_expired = $doc->expiry_date->isPast();
        });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function isExpiringSoon()
    {
        return $this->expiry_date->diffInDays(now()) <= $this->alert_before_days;
    }
    public function daysUntilExpiry()
    {
        return max(0, (int) now()->diffInDays($this->expiry_date, false));
    }
}
