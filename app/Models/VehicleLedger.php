<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VehicleLedger extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'entry_type',
        'category',
        'reference_type',
        'reference_id',
        'description',
        'amount',
        'entry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
