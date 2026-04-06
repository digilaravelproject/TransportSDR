<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Lead extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'lead_number',
        'enquiry_date',
        'trip_route',
        'trip_date',
        'return_date',
        'duration_days',
        'vehicle_type',
        'seating_capacity',
        'number_of_vehicles',
        'pickup_address',
        'destination_points',
        'customer_name',
        'customer_contact',
        'customer_email',
        'customer_id',
        'quoted_amount',
        'advance_amount',
        'discount',
        'is_gst',
        'gst_percent',
        'tax_amount',
        'total_with_tax',
        'quotation_path',
        'bill_path',
        'quotation_sent_at',
        'status',
        'source',
        'notes',
        'followup_date',
        'followup_notes',
        'converted_trip_id',
        'converted_at',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'enquiry_date'      => 'date',
        'trip_date'         => 'date',
        'return_date'       => 'date',
        'followup_date'     => 'date',
        'converted_at'      => 'datetime',
        'quotation_sent_at' => 'datetime',
        'destination_points' => 'array',
        'is_gst'            => 'boolean',
        'quoted_amount'     => 'decimal:2',
        'advance_amount'    => 'decimal:2',
        'discount'          => 'decimal:2',
        'tax_amount'        => 'decimal:2',
        'total_with_tax'    => 'decimal:2',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function convertedTrip()
    {
        return $this->belongsTo(Trip::class, 'converted_trip_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Auto lead number
    // protected static function booted(): void
    // {
    //     static::creating(function ($lead) {
    //         $year             = now()->format('Y');
    //         $count            = self::withoutGlobalScopes()->whereYear('created_at', $year)->count() + 1;
    //         $lead->lead_number = 'LID-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    //         $lead->created_by  = auth()->id();

    //         if (empty($lead->enquiry_date)) {
    //             $lead->enquiry_date = now()->toDateString();
    //         }
    //     });
    // }
    protected static function booted(): void
    {
        static::creating(function ($lead) {
            $year              = now()->format('Y');
            $count             = self::withoutGlobalScopes()->whereYear('created_at', $year)->count() + 1;
            $lead->lead_number = 'LID-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $lead->created_by  = auth()->id();
            if (empty($lead->enquiry_date)) {
                $lead->enquiry_date = now()->toDateString();
            }
        });

        static::saving(function ($lead) {
            // Auto calculate GST
            if ($lead->is_gst && $lead->gst_percent > 0) {
                $lead->tax_amount = round($lead->quoted_amount * $lead->gst_percent / 100, 2);
            } else {
                $lead->tax_amount = 0;
            }
            // Total with tax minus discount
            $lead->total_with_tax = $lead->quoted_amount + $lead->tax_amount - $lead->discount;
        });
    }
    // Scopes
    public function scopeNew($q)
    {
        return $q->where('status', 'new');
    }
    public function scopeFollowup($q)
    {
        return $q->where('status', 'followup');
    }
    public function scopeConverted($q)
    {
        return $q->where('status', 'converted');
    }
    public function scopePending($q)
    {
        return $q->whereNotIn('status', ['converted', 'lost', 'cancelled']);
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }
    public function isLost(): bool
    {
        return in_array($this->status, ['lost', 'cancelled']);
    }
}
