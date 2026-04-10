<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CorporateFine extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'corporate_id',
        'duty_id',
        'reason',
        'amount',
        'fine_date',
        'status',
        'payment_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'fine_date' => 'date',
        'amount'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function corporate()
    {
        return $this->belongsTo(Corporate::class);
    }
    public function duty()
    {
        return $this->belongsTo(CorporateDuty::class, 'duty_id');
    }
    public function payment()
    {
        return $this->belongsTo(CorporatePayment::class, 'payment_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
