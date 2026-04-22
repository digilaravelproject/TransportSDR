@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <div>
        <span class="text-muted">Welcome, {{ Auth::guard('admin')->user()->name }}</span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Users</h5>
                        <h3>{{ \App\Models\User::count() }}</h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Tenants</h5>
                        <h3>{{ \App\Models\Tenant::count() }}</h3>
                    </div>
                    <i class="fas fa-building fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Vehicles</h5>
                        <h3>{{ \App\Models\Vehicle::count() }}</h3>
                    </div>
                    <i class="fas fa-truck fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Trips</h5>
                        <h3>{{ \App\Models\Trip::count() }}</h3>
                    </div>
                    <i class="fas fa-route fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Recent Activity</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Activity feed will be displayed here.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-users me-2"></i>
                    Manage Users
                </a>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-list me-2"></i>
                    View Plans
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-success w-100">
                    <i class="fas fa-credit-card me-2"></i>
                    View Subscriptions
                </a>
            </div>
        </div>
    </div>
</div>
@endsection