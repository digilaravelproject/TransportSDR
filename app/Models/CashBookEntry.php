<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class CashBookEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'entry_type',
        'payment_mode',
        'category',
        'amount',
        'description',
        'entry_date',
        'reference_number',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Optional but useful)
    |--------------------------------------------------------------------------
    */

    public function scopeIncome($query)
    {
        return $query->where('entry_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('entry_type', 'expense');
    }
}