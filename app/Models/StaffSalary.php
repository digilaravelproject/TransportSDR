<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class StaffSalary extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'month',
        'year',
        'basic_salary',
        'hra',
        'da_total',
        'bonus',
        'other_allowance',
        'gross_salary',
        'advance_deduction',
        'absent_deduction',
        'other_deduction',
        'total_deduction',
        'net_salary',
        'total_days',
        'present_days',
        'absent_days',
        'half_days',
        'trip_days',
        'payment_status',
        'payment_mode',
        'paid_on',
        'transaction_ref',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'paid_on'          => 'date',
        'basic_salary'     => 'decimal:2',
        'hra'              => 'decimal:2',
        'da_total'         => 'decimal:2',
        'bonus'            => 'decimal:2',
        'other_allowance'  => 'decimal:2',
        'gross_salary'     => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'other_deduction'  => 'decimal:2',
        'total_deduction'  => 'decimal:2',
        'net_salary'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());

        static::saving(function ($sal) {
            $sal->gross_salary   = $sal->basic_salary + $sal->hra + $sal->da_total
                + $sal->bonus + $sal->other_allowance;
            $sal->total_deduction = $sal->advance_deduction + $sal->absent_deduction
                + $sal->other_deduction;
            $sal->net_salary      = $sal->gross_salary - $sal->total_deduction;
        });
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    public function advances()
    {
        return $this->hasMany(StaffAdvance::class, 'salary_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
