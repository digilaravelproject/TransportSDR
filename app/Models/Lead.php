<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;
use Carbon\Carbon;
class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'lead_number',
        'trip_route',
        'trip_date',
        'duration_days',
        'vehicle_type',
        'seating_capacity',
        'pickup_address',
        'points',
        'customer_name',
        'customer_contact',
        'total_amount',
        'advance_amount',
        'pending_amount',
        'status',
        'quotation_path',
        'vehicle_id',
        'driver_id',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'points'    => 'array',
        'total_amount'   => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($lead) {
            $year = now()->format('Y');
            $count = self::whereYear('created_at', $year)->count() + 1;
            $lead->lead_number = 'LD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            if (empty($lead->status)) {
                $lead->status = 'pending';
            }
        });
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->orderBy('created_at', 'desc');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowUp::class)->orderBy('reminder_at', 'desc');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'driver_id');
    }

    public function expenses()
    {
        return $this->hasMany(LeadExpense::class)->orderBy('created_at', 'desc');
    }

    public function dutySheets()
    {
        return $this->hasMany(LeadDutySheet::class)->orderBy('created_at', 'desc');
    }

    // Provide compatibility accessors used by QuotationService
    public function getDestinationPointsAttribute()
    {
        return $this->points;
    }

    public function getQuotedAmountAttribute()
    {
        return $this->total_amount ?? 0;
    }

    public function getDiscountAttribute()
    {
        return $this->attributes['discount'] ?? 0;
    }

    public function getIsGstAttribute()
    {
        return (bool) ($this->attributes['is_gst'] ?? false);
    }

    public function getGstPercentAttribute()
    {
        return $this->attributes['gst_percent'] ?? 0;
    }

    public function getTaxAmountAttribute()
    {
        return $this->attributes['tax_amount'] ?? 0;
    }

    public function getTotalWithTaxAttribute()
    {
        return $this->attributes['total_with_tax'] ?? ($this->total_amount ?? 0);
    }

    public function getNumberOfVehiclesAttribute()
    {
        return $this->attributes['number_of_vehicles'] ?? 1;
    }

    public function getReturnDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getCustomerEmailAttribute()
    {
        return $this->attributes['customer_email'] ?? null;
    }
}
