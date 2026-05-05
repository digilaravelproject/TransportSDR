
@extends('admin.layout')

@section('title', 'Create Vehicle Type')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold">Create Vehicle Type</h2>
        <p class="text-muted">Add a new vehicle type</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.vehicle-types.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Capacity</label>
                    <input name="capacity" class="form-control" type="number">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Price / KM</label>
                    <input name="price_per_km" class="form-control" type="number" step="0.01">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">AC Extra Price / KM</label>
                    <input name="ac_extra_price" class="form-control" type="number" step="0.01">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button class="btn btn-primary">Create Type</button>
        </form>
    </div>
</div>

@endsection
