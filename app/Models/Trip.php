<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Trip extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'trip_number',
        'trip_date',
        'return_date',
        'duration_days',
        'trip_route',
        'pickup_address',
        'destination_points',
        'vehicle_id',
        'vehicle_type',
        'seating_capacity',
        'number_of_vehicles',
        'customer_id',
        'customer_name',
        'customer_contact',
        'driver_id',
        'helper_id',
        'start_km',
        'end_km',
        'total_km',
        'km_grade',
        'total_amount',
        'advance_amount',
        'part_payment',
        'balance_amount',
        'discount',
        'is_gst',
        'gst_percent',
        'tax_amount',
        'payment_status',
        'status',
        'duty_slip_path',
        'invoice_path',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'trip_date'          => 'date',
        'return_date'        => 'date',
        'destination_points' => 'array',
        'is_gst'             => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function driver()
    {
        return $this->belongsTo(Staff::class, 'driver_id');
    }
    public function helper()
    {
        return $this->belongsTo(Staff::class, 'helper_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function payments()
    {
        return $this->hasMany(TripPayment::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($trip) {
            $year         = now()->format('Y');
            $count        = self::withoutGlobalScopes()->whereYear('created_at', $year)->count() + 1;
            $trip->trip_number = 'TRP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $trip->created_by  = auth()->id();
        });

        static::saving(function ($trip) {
            if ($trip->start_km && $trip->end_km) {
                $trip->total_km = max(0, $trip->end_km - $trip->start_km);
                $trip->km_grade = match (true) {
                    $trip->total_km <= 100  => 'A',
                    $trip->total_km <= 300  => 'B',
                    $trip->total_km <= 600  => 'C',
                    default                 => 'D',
                };
            }
            if ($trip->is_gst && $trip->gst_percent > 0) {
                $trip->tax_amount = round($trip->total_amount * $trip->gst_percent / 100, 2);
            }
            $paid = $trip->advance_amount + $trip->part_payment;
            $trip->balance_amount = ($trip->total_amount + $trip->tax_amount - $trip->discount) - $paid;
            $trip->payment_status = $trip->balance_amount <= 0 ? 'paid'
                : ($paid > 0 ? 'partial' : 'pending');
        });
    }
}
