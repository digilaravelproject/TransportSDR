<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class OnlinePayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'gateway',
        'reference_type',
        'reference_id',
        'reference_number',
        'transaction_id',
        'gateway_order_id',
        'gateway_payment_id',
        'amount',
        'currency',
        'payer_name',
        'payer_contact',
        'payer_upi_id',
        'payer_bank',
        'status',
        'refund_amount',
        'paid_at',
        'gateway_response',
        'failure_reason',
        'alert_sent',
        'alert_sent_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'paid_at'       => 'datetime',
        'alert_sent_at' => 'datetime',
        'alert_sent'    => 'boolean',
        'amount'        => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
