<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class StaffAdvance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'amount',
        'advance_date',
        'reason',
        'payment_mode',
        'transaction_ref',
        'is_deducted',
        'salary_id',
        'deducted_on',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'deducted_on'  => 'date',
        'is_deducted'  => 'boolean',
        'amount'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    public function salary()
    {
        return $this->belongsTo(StaffSalary::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
