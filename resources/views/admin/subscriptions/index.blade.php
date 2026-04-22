@extends('admin.layout')

@section('title', 'Manage Subscriptions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Subscriptions</h2>
    <button class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        Add Subscription
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
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
                        <td>{{ $subscription['id'] }}</td>
                        <td>
                            <strong>{{ $subscription['tenant_name'] }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $subscription['plan_name'] }}</span>
                        </td>
                        <td>{{ $subscription['email'] }}</td>
                        <td><strong>₹{{ $subscription['amount'] }}</strong></td>
                        <td>{{ $subscription['start_date'] }}</td>
                        <td>{{ $subscription['end_date'] }}</td>
                        <td>
                            @if($subscription['status'] == 'Active')
                                <span class="badge bg-success">Active</span>
                            @elseif($subscription['status'] == 'Expired')
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-warning">{{ $subscription['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
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
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Active Subscriptions</h5>
                        <h3>{{ collect($subscriptions)->where('status', 'Active')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Expired Subscriptions</h5>
                        <h3>{{ collect($subscriptions)->where('status', 'Expired')->count() }}</h3>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Revenue</h5>
                        <h3>₹{{ collect($subscriptions)->sum('amount') }}</h3>
                    </div>
                    <i class="fas fa-rupiah-sign fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection