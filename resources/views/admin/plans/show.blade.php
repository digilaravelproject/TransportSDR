@extends('admin.layout')

@section('title', $plan->name)

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">{{ $plan->name }}</h2>
        <p class="text-muted">{{ $plan->description }}</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Plans
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Plan Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Price</h6>
                        <p class="h3">RS. {{ number_format($plan->price, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Duration</h6>
                        <p class="h5">{{ ucfirst($plan->duration) }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        @if($plan->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Billing Cycle</h6>
                        <p>{{ $plan->billing_cycle_days }} days</p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3">Limitations</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p>
                            <strong>Max Vehicles:</strong>
                            @if($plan->max_vehicles)
                                {{ $plan->max_vehicles }}
                            @else
                                <span class="badge bg-success">Unlimited</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Max Trips/Month:</strong>
                            @if($plan->max_trips_per_month)
                                {{ $plan->max_trips_per_month }}
                            @else
                                <span class="badge bg-success">Unlimited</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Max Staff:</strong>
                            @if($plan->max_staff)
                                {{ $plan->max_staff }}
                            @else
                                <span class="badge bg-success">Unlimited</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Sort Order:</strong> {{ $plan->sort_order }}
                        </p>
                    </div>
                </div>


                <hr>

                <h6 class="mb-3">Modules</h6>
                @if($plan->module_access_array)
                    <ul class="list-unstyled">
                        @foreach($plan->module_access_array as $module)
                            <li class="mb-2">
                                <i class="fas fa-cube text-primary me-2"></i>
                                {{ $module }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No modules assigned</p>
                @endif

                <hr>

                <h6 class="mb-3">Features</h6>
                @if($plan->features)
                    <ul class="list-unstyled">
                        @foreach($plan->features as $feature)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No features listed</p>
                @endif

                <hr>

                <div class="row text-muted small">
                    <div class="col-md-6">
                        <p><strong>Created:</strong> {{ $plan->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Updated:</strong> {{ $plan->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i> Edit Plan
                </a>
                <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this plan?');">
                        <i class="fas fa-trash me-2"></i> Delete Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Plans
    </a>
</div>
@endsection
