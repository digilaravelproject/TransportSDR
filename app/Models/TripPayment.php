<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class TripPayment extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'trip_id',
        'amount',
        'type',
        'mode',
        'reference',
        'paid_on',
        'collected_by',
        'notes',
        'created_by',
    ];
    protected $casts = ['paid_on' => 'date', 'amount' => 'decimal:2'];
    protected static function booted(): void
    {
        static::creating(fn($p) => $p->created_by = auth()->id());
    }
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
