<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'plan_id',
        'status',
        'payment_status',
        'start_date',
        'end_date',
        'next_billing_date',
        'amount',
        'tax_amount',
        'total_amount',
        'billing_cycle',
        'billing_cycle_days',
        'razorpay_subscription_id',
        'razorpay_payment_id',
        'razorpay_customer_id',
        'razorpay_invoice_id',
        'renewal_count',
        'notes',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'renewal_count' => 'integer',
    ];

    /**
     * Get the user associated with the subscription
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant associated with the subscription
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the plan associated with the subscription
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopeExpiring($query)
    {
        // Subscriptions expiring in 7 days
        return $query->whereBetween('end_date', [
            now(),
            now()->addDays(7)
        ]);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhere('end_date', '<', now());
    }

    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->payment_status === 'completed' &&
               (!$this->end_date || $this->end_date > now());
    }

    /**
     * Check if subscription is expiring soon
     */
    public function isExpiringSoon(): bool
    {
        return $this->end_date && 
               $this->end_date->greaterThan(now()) &&
               $this->end_date->lessThanOrEqualTo(now()->addDays(7));
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->end_date && $this->end_date < now());
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Calculate renewal dates
     */
    public function calculateRenewalDates()
    {
        $user = $this->user ?? \App\Models\User::find($this->user_id);
        
        if (!$this->start_date) {
            $trialEndDate = $user ? $user->created_at->copy()->addDays(15) : null;
            
            if ($trialEndDate && now()->lessThan($trialEndDate)) {
                $this->start_date = $trialEndDate;
            } else {
                $this->start_date = now();
            }
        }
        
        $startDate = \Carbon\Carbon::parse($this->start_date);
        $this->end_date = $startDate->copy()->addDays($this->billing_cycle_days);
        $this->next_billing_date = $this->end_date;
    }

    /**
     * Renew subscription
     */
    public function renew()
    {
        $this->renewal_count++;
        $this->start_date = now();
        $this->end_date = now()->addDays($this->billing_cycle_days);
        $this->next_billing_date = $this->end_date;
        $this->status = 'active';
        $this->save();

        return $this;
    }

    /**
     * Cancel subscription
     */
    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        $this->payment_status = 'refunded';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->save();

        return $this;
    }

    /**
     * Pause subscription
     */
    public function pause()
    {
        $this->status = 'paused';
        $this->save();

        return $this;
    }

    /**
     * Resume subscription
     */
    public function resume()
    {
        $this->status = 'active';
        $this->save();

        return $this;
    }
}
