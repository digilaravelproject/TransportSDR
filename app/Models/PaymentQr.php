<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class PaymentQr extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'qr_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'upi_id',
        'payee_name',
        'amount',
        'transaction_note',
        'currency',
        'qr_image_path',
        'upi_deep_link',
        'expires_at',
        'is_active',
        'send_alert',
        'alert_contact',
        'alert_sent',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
        'send_alert' => 'boolean',
        'alert_sent' => 'boolean',
        'amount'     => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_image_path ? asset("storage/{$this->qr_image_path}") : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
