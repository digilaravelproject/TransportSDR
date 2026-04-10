<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class CorporateDuty extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'corporate_id',
        'duty_number',
        'duty_date',
        'duty_type',
        'duty_status',
        'shift_name',
        'shift_start',
        'shift_end',
        'vehicle_id',
        'vehicle_type',
        'number_of_vehicles',
        'driver_id',
        'helper_id',
        'pickup_location',
        'drop_location',
        'route_details',
        'start_km',
        'end_km',
        'total_km',
        'extra_km',
        'total_hours',
        'extra_hours',
        'is_holiday',
        'is_extra_duty',
        'base_amount',
        'extra_km_amount',
        'extra_hour_amount',
        'holiday_amount',
        'extra_duty_amount',
        'fine_amount',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'duty_date'  => 'date',
        'is_holiday' => 'boolean',
        'is_extra_duty' => 'boolean',
        'start_km'   => 'decimal:2',
        'end_km'     => 'decimal:2',
        'total_km'   => 'decimal:2',
        'extra_km'   => 'decimal:2',
        'total_hours' => 'decimal:2',
        'extra_hours' => 'decimal:2',
        'base_amount'       => 'decimal:2',
        'extra_km_amount'   => 'decimal:2',
        'extra_hour_amount' => 'decimal:2',
        'holiday_amount'    => 'decimal:2',
        'extra_duty_amount' => 'decimal:2',
        'fine_amount'       => 'decimal:2',
        'total_amount'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($duty) {
            $duty->created_by = auth()->id();
            $year  = now()->format('Y');
            $count = self::withoutGlobalScopes()->whereYear('created_at', $year)->count() + 1;
            $duty->duty_number = 'DUT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });

        static::saving(function ($duty) {
            $corporate = Corporate::find($duty->corporate_id);
            if (!$corporate) return;

            // KM calculation
            if ($duty->start_km && $duty->end_km) {
                $duty->total_km = max(0, $duty->end_km - $duty->start_km);
                $extraKm = max(0, $duty->total_km - $corporate->included_km);
                $duty->extra_km        = $extraKm;
                $duty->extra_km_amount = round($extraKm * $corporate->per_km_rate, 2);
            }

            // Extra hours amount
            if ($duty->extra_hours > 0) {
                $duty->extra_hour_amount = round($duty->extra_hours * $corporate->extra_hour_rate, 2);
            }

            // Holiday amount
            $duty->holiday_amount = $duty->is_holiday ? $corporate->holiday_rate : 0;

            // Extra duty amount
            $duty->extra_duty_amount = $duty->is_extra_duty ? $corporate->extra_duty_rate : 0;

            // Base amount
            $duty->base_amount = match ($corporate->contract_type) {
                'monthly' => 0, // monthly me individual duty ka amount nahi hota
                'daily'   => $corporate->per_day_rate,
                default   => 0,
            };

            // Total
            $duty->total_amount = $duty->base_amount
                + $duty->extra_km_amount
                + $duty->extra_hour_amount
                + $duty->holiday_amount
                + $duty->extra_duty_amount
                - $duty->fine_amount;
        });
    }

    public function corporate()
    {
        return $this->belongsTo(Corporate::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function driver()
    {
        return $this->belongsTo(Staff::class, 'driver_id');
    }
    public function helper()
    {
        return $this->belongsTo(Staff::class, 'helper_id');
    }
    public function fines()
    {
        return $this->hasMany(CorporateFine::class, 'duty_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
