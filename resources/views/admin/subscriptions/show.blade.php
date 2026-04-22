@extends('admin.layout')

@section('title', 'Subscription Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Subscription Details #{{ $subscription->id }}</h2>
    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Subscriptions
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>User Name:</strong>
                        <p class="mb-0">{{ $subscription->user ? $subscription->user->name : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>User Email:</strong>
                        <p class="mb-0">{{ $subscription->user ? $subscription->user->email : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Plan Name:</strong>
                        <p class="mb-0">
                            <span class="badge bg-info p-2">{{ $subscription->plan ? $subscription->plan->name : 'N/A' }}</span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Amount / Status:</strong>
                        <p class="mb-0 text-success">
                            <strong>₹{{ number_format($subscription->total_amount, 2) }}</strong> 
                            ({{ ucfirst($subscription->payment_status) }})
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Start Date:</strong>
                        <p class="mb-0">{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d H:i') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>End Date:</strong>
                        <p class="mb-0">{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d H:i') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Razorpay Subscription ID:</strong>
                        <p class="mb-0">{{ $subscription->razorpay_subscription_id ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-{{ $subscription->status == 'active' ? 'success' : 'warning' }} p-2">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </p>
                    </div>
                    @if($subscription->cancellation_reason)
                    <div class="col-md-12 mb-3">
                        <strong>Cancellation Reason:</strong>
                        <p class="mb-0 text-muted">{{ $subscription->cancellation_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Subscription
                </a>
                
                @if($subscription->status == 'active')
                    <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Cancel this subscription?');">
                            <i class="fas fa-times-circle me-1"></i> Cancel Subscription
                        </button>
                    </form>
                @endif
                
                @if($subscription->status == 'expired' || $subscription->status == 'cancelled')
                    <form action="{{ route('admin.subscriptions.renew', $subscription->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Renew this subscription?');">
                            <i class="fas fa-sync me-1"></i> Renew Subscription
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
