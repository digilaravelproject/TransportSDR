<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CorporatePayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'corporate_id',
        'invoice_number',
        'billing_period',
        'billing_from',
        'billing_to',
        'total_duties',
        'holiday_duties',
        'extra_duties',
        'total_km',
        'extra_km',
        'base_amount',
        'extra_km_amount',
        'extra_hour_amount',
        'holiday_amount',
        'extra_duty_amount',
        'fine_deduction',
        'subtotal',
        'is_gst',
        'gst_percent',
        'cgst',
        'sgst',
        'igst',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'payment_mode',
        'paid_on',
        'transaction_ref',
        'invoice_path',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'billing_from' => 'date',
        'billing_to'   => 'date',
        'paid_on'      => 'date',
        'is_gst'       => 'boolean',
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($p) {
            $p->created_by = auth()->id();
            $year  = now()->format('Y');
            $count = self::withoutGlobalScopes()->whereYear('created_at', $year)->count() + 1;
            $p->invoice_number = 'CINV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });

        static::saving(function ($p) {
            // GST calculation
            if ($p->is_gst && $p->gst_percent > 0) {
                $halfGst      = $p->gst_percent / 2;
                $p->cgst      = round($p->subtotal * $halfGst / 100, 2);
                $p->sgst      = round($p->subtotal * $halfGst / 100, 2);
                $p->igst      = 0;
                $p->tax_amount = $p->cgst + $p->sgst;
            } else {
                $p->cgst = $p->sgst = $p->igst = $p->tax_amount = 0;
            }
            $p->total_amount  = $p->subtotal + $p->tax_amount;
            $p->balance_amount = $p->total_amount - $p->paid_amount;

            if ($p->balance_amount <= 0) {
                $p->payment_status = 'paid';
            } elseif ($p->paid_amount > 0) {
                $p->payment_status = 'partial';
            } else {
                $p->payment_status = 'pending';
            }
        });
    }

    public function corporate()
    {
        return $this->belongsTo(Corporate::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
