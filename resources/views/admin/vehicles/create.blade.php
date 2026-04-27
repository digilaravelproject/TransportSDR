@extends('admin.layout')

@section('title', 'Create Vehicle')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Create Vehicle</h2>
        <p class="text-muted">Add a new vehicle</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tenant *</label>
                        <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                            <option value="">-- Select Tenant --</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->company_name }}</option>
                            @endforeach
                        </select>
                        @error('tenant_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registration Number *</label>
                        <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" required>
                        @error('registration_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" class="form-control" value="{{ old('type') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Seating Capacity</label>
                            <input type="number" name="seating_capacity" class="form-control" value="{{ old('seating_capacity') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Model Year</label>
                            <input type="number" name="model_year" class="form-control" value="{{ old('model_year') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Per Km Price</label>
                            <input type="text" name="per_km_price" class="form-control" value="{{ old('per_km_price') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RC Number</label>
                        <input type="text" name="rc_number" class="form-control" value="{{ old('rc_number') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RC Expiry</label>
                            <input type="date" name="rc_expiry" class="form-control" value="{{ old('rc_expiry') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RC File</label>
                            <input type="file" name="rc_file" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Insurance Number</label>
                        <input type="text" name="insurance_number" class="form-control" value="{{ old('insurance_number') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Insurance Expiry</label>
                            <input type="date" name="insurance_expiry" class="form-control" value="{{ old('insurance_expiry') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Insurance File</label>
                            <input type="file" name="insurance_file" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permit Number</label>
                        <input type="text" name="permit_number" class="form-control" value="{{ old('permit_number') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Permit Expiry</label>
                            <input type="date" name="permit_expiry" class="form-control" value="{{ old('permit_expiry') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Permit File</label>
                            <input type="file" name="permit_file" class="form-control">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_available" name="is_available">
                        <label class="form-check-label" for="is_available">Available</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
