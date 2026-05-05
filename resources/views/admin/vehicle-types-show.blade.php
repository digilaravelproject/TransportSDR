@extends('admin.layout')

@section('title', 'View Vehicle Type')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold">View Vehicle Type</h2>
        <p class="text-muted">Details for {{ $vehicleType->name }}</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $vehicleType->name }}</dd>

            <dt class="col-sm-3">Capacity</dt>
            <dd class="col-sm-9">{{ $vehicleType->capacity }}</dd>

            <dt class="col-sm-3">Price / KM</dt>
            <dd class="col-sm-9">{{ number_format($vehicleType->price_per_km,2) }}</dd>

            <dt class="col-sm-3">AC Extra</dt>
            <dd class="col-sm-9">{{ number_format($vehicleType->ac_extra_price,2) }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $vehicleType->is_active ? 'Active' : 'Inactive' }}</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $vehicleType->description }}</dd>
        </dl>
    </div>
</div>

@endsection