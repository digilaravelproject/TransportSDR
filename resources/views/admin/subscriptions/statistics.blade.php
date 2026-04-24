@extends('admin.layout')

@section('title', 'Subscription Statistics')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Subscription Statistics</h2>
        <p class="text-muted">Overview of subscription metrics</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">30-Day Revenue</h5>
                <h3>₹{{ number_format($monthlyRevenue, 2) }}</h3>
                <p class="mb-0"><small>From completed payments</small></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Expiring Soon</h5>
                <h3>{{ $expiringSubscriptions }}</h3>
                <p class="mb-0"><small>Within the next 7 days</small></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Expired</h5>
                <h3>{{ $expiredSubscriptions }}</h3>
                <p class="mb-0"><small>Total expired subscriptions</small></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Active Subscriptions by Plan</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($subscriptionsByPlan as $stat)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $stat->plan ? $stat->plan->name : 'Unknown Plan' }}
                            <span class="badge bg-primary rounded-pill">{{ $stat->total }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No active subscriptions by plan yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Total Subscriptions by Status</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($subscriptionsByStatus as $status => $count)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucfirst($status) }}
                            <span class="badge bg-{{ $status=='active' ? 'success' : ($status=='expired' ? 'danger' : 'secondary') }} rounded-pill">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No statuses found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
