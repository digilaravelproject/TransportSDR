<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Corporate extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'gstin',
        'pan',
        'contract_type',
        'monthly_package',
        'per_day_rate',
        'per_km_rate',
        'extra_hour_rate',
        'holiday_rate',
        'extra_duty_rate',
        'included_km',
        'included_hours',
        'vehicle_type',
        'number_of_vehicles',
        'duty_type',
        'is_gst',
        'gst_percent',
        'is_active',
        'contract_start',
        'contract_end',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'contract_start'   => 'date',
        'contract_end'     => 'date',
        'is_gst'           => 'boolean',
        'is_active'        => 'boolean',
        'monthly_package'  => 'decimal:2',
        'per_day_rate'     => 'decimal:2',
        'per_km_rate'      => 'decimal:2',
        'extra_hour_rate'  => 'decimal:2',
        'holiday_rate'     => 'decimal:2',
        'extra_duty_rate'  => 'decimal:2',
        'included_km'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function duties()
    {
        return $this->hasMany(CorporateDuty::class);
    }
    public function payments()
    {
        return $this->hasMany(CorporatePayment::class);
    }
    public function fines()
    {
        return $this->hasMany(CorporateFine::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pendingFinesAmount(): float
    {
        return (float) $this->fines()->where('status', 'pending')->sum('amount');
    }
}
