@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Dashboard Overview</h2>
        <p class="text-muted">Welcome back, <span class="fw-semibold text-dark">{{ Auth::guard('admin')->user()->name }}</span></p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small fw-bold">TOTAL USERS</p>
                        <h3 class="fw-bold mb-0">{{ \App\Models\User::count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-building fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small fw-bold">TOTAL TENANTS</p>
                        <h3 class="fw-bold mb-0">{{ \App\Models\Tenant::count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-truck fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small fw-bold">VEHICLES</p>
                        <h3 class="fw-bold mb-0">{{ \App\Models\Vehicle::count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-route fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small fw-bold">TOTAL TRIPS</p>
                        <h3 class="fw-bold mb-0">{{ \App\Models\Trip::count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/data-analysis.svg" style="width: 150px;" class="mb-3 opacity-50">
                    <p class="text-muted">Activity feed will be displayed here as it happens.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Quick Actions</h5>
            </div>
            <div class="card-body p-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary w-100 mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-user-plus me-2"></i> Manage Users</span>
                    <i class="fas fa-chevron-right small"></i>
                </a>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-info w-100 mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-layer-group me-2"></i> Subscription Plans</span>
                    <i class="fas fa-chevron-right small"></i>
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-wallet me-2"></i> Revenue Overview</span>
                    <i class="fas fa-chevron-right small"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection