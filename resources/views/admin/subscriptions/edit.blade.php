@extends('admin.layout')

@section('title', 'Edit Subscription')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Edit Subscription #{{ $subscription->id }}</h2>
        <p class="text-muted">Modify plan and status for this subscription</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Details
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.subscriptions.update', $subscription->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Plan</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (₹{{ $plan->price }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subscription Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ $subscription->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ $subscription->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paused" {{ $subscription->status == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="expired" {{ $subscription->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ $subscription->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select" required>
                        <option value="completed" {{ $subscription->payment_status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ $subscription->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ $subscription->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ $subscription->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $subscription->notes }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update Subscription
            </button>
        </form>
    </div>
</div>
@endsection
