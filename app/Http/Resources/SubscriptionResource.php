<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tenant_id' => $this->tenant_id,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'is_cancelled' => $this->isCancelled(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'next_billing_date' => $this->next_billing_date?->toIso8601String(),
            'amount' => (float) $this->amount,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'billing_cycle' => $this->billing_cycle,
            'billing_cycle_days' => $this->billing_cycle_days,
            'renewal_count' => $this->renewal_count,
            'razorpay_subscription_id' => $this->razorpay_subscription_id,
            'razorpay_payment_id' => $this->razorpay_payment_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
