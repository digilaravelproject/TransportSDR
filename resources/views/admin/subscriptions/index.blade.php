@extends('admin.layout')

@section('title', 'Manage Subscriptions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Subscriptions</h2>
    <div>
        <a href="{{ route('admin.subscriptions.export') }}" class="btn btn-success me-2">
            <i class="fas fa-file-excel me-2"></i>Export CSV
        </a>
        <a href="{{ route('admin.subscriptions.statistics') }}" class="btn btn-info text-white">
            <i class="fas fa-chart-pie me-2"></i>Statistics
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="row">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by name/email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="payment_status" class="form-select">
                    <option value="">All Payment Statuses</option>
                    <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Plan</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->id }}</td>
                        <td>
                            <strong>{{ $subscription->user ? $subscription->user->name : 'N/A' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $subscription->plan ? $subscription->plan->name : 'N/A' }}</span>
                        </td>
                        <td>{{ $subscription->user ? $subscription->user->email : 'N/A' }}</td>
                        <td><strong>₹{{ $subscription->total_amount }}</strong></td>
                        <td>{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : '-' }}</td>
                        <td>{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : '-' }}</td>
                        <td>
                            @if($subscription->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($subscription->status == 'expired' || $subscription->status == 'cancelled')
                                <span class="badge bg-danger">{{ ucfirst($subscription->status) }}</span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($subscription->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-outline-info me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No subscriptions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $subscriptions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stats-card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Active Subscriptions</h5>
                        <h3>{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Expired Subscriptions</h5>
                        <h3>{{ $stats['expired'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Revenue</h5>
                        <h3>₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                    </div>
                    <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection