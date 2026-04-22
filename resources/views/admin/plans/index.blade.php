@extends('admin.layout')

@section('title', 'Manage Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Plans</h2>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        Add Plan
    </a>
</div>

@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    @forelse($plans as $plan)
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $plan->name }}</h5>
                    @if($plan->status == 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <h3 class="mb-1">RS. {{ number_format($plan->price, 2) }} <span class="h6 text-muted">/ {{ ucfirst($plan->duration) }}</span></h3>
                
                <p class="text-muted small">{{ $plan->description }}</p>
                
                <hr>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Vehicles</small>
                        <p class="mb-0"><strong>{{ $plan->max_vehicles ?? 'Unlimited' }}</strong></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Trips/Month</small>
                        <p class="mb-0"><strong>{{ $plan->max_trips_per_month ?? 'Unlimited' }}</strong></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Staff</small>
                        <p class="mb-0"><strong>{{ $plan->max_staff ?? 'Unlimited' }}</strong></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Billing Cycle</small>
                        <p class="mb-0"><strong>{{ $plan->billing_cycle_days }} days</strong></p>
                    </div>
                </div>

                <h6 class="mb-2">Features:</h6>
                <ul class="list-unstyled mb-3">
                    @if($plan->features)
                        @foreach($plan->features as $feature)
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    @endif
                </ul>

                <small class="text-muted">Created: {{ $plan->created_at->format('d M Y') }}</small>
            </div>
            <div class="card-footer bg-light">
                <a href="{{ route('admin.plans.show', $plan->id) }}" class="btn btn-sm btn-outline-info me-1">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary me-1">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            No plans found. <a href="{{ route('admin.plans.create') }}">Create one</a>
        </div>
    </div>
    @endforelse
</div>

@if($plans->hasPages())
<nav aria-label="Page navigation">
    {{ $plans->links('pagination::bootstrap-4') }}
</nav>
@endif
@endsection