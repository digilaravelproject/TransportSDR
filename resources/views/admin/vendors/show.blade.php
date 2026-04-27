@extends('admin.layout')

@section('title', 'Vendor Details')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Vendor Details</h2>
        <p class="text-muted">View vendor information</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">{{ $vendor->vendor_name }} — {{ $vendor->contract_name }}</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tenant:</strong> {{ $vendor->tenant->company_name ?? '—' }}</p>
                <p><strong>Start Date:</strong> {{ optional($vendor->start_date)->format('Y-m-d') }}</p>
                <p><strong>End Date:</strong> {{ optional($vendor->end_date)->format('Y-m-d') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Vehicle Type:</strong> {{ $vendor->vehicle_type ?? '—' }}</p>
                <p><strong>Quantity:</strong> {{ $vendor->quantity ?? '—' }}</p>
                <p><strong>Monthly Amount:</strong> {{ $vendor->monthly_amount ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

@endsection
