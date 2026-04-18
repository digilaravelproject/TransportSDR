<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class CashBookEntry extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'entry_type',
        'payment_mode',
        'category',
        'reference_type',
        'reference_id',
        'reference_number',
        'amount',
        'opening_balance',
        'closing_balance',
        'description',
        'entry_date',
        'party_name',
        'party_contact',
        'transaction_id',
        'bank_name',
        'cheque_number',
        'cheque_date',
        'status',
        'notes',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'entry_date'   => 'date',
        'cheque_date'  => 'date',
        'amount'       => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
