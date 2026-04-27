@extends('admin.layout')

@section('title', 'Vehicle Details')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Vehicle Details</h2>
        <p class="text-muted">View vehicle information</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">{{ $vehicle->registration_number }} — {{ $vehicle->type }}</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tenant:</strong> {{ $vehicle->tenant->company_name ?? '—' }}</p>
                <p><strong>Model Year:</strong> {{ $vehicle->model_year ?? '—' }}</p>
                <p><strong>Seating Capacity:</strong> {{ $vehicle->seating_capacity ?? '—' }}</p>
                <p><strong>Per Km Price:</strong> {{ $vehicle->per_km_price ?? '—' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>RC Number:</strong> {{ $vehicle->rc_number ?? '—' }}</p>
                <p><strong>Insurance Number:</strong> {{ $vehicle->insurance_number ?? '—' }}</p>
                <p><strong>Permit Number:</strong> {{ $vehicle->permit_number ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

@endsection
