@extends('admin.layout')

@section('title', 'Edit Vendor')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Edit Vendor</h2>
        <p class="text-muted">Update vendor details</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vendors.update', $vendor->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Tenant *</label>
                        <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                            <option value="">-- Select Tenant --</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}" {{ $vendor->tenant_id == $t->id ? 'selected' : '' }}>{{ $t->company_name }}</option>
                            @endforeach
                        </select>
                        @error('tenant_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vendor Name *</label>
                        <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror" value="{{ old('vendor_name', $vendor->vendor_name) }}" required>
                        @error('vendor_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contract Name</label>
                        <input type="text" name="contract_name" class="form-control" value="{{ old('contract_name', $vendor->contract_name) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($vendor->start_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($vendor->end_date)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duty Type</label>
                        <input type="text" name="duty_type" class="form-control" value="{{ old('duty_type', $vendor->duty_type) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Type</label>
                        <input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type', $vendor->vehicle_type) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $vendor->quantity) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monthly Amount</label>
                            <input type="text" name="monthly_amount" class="form-control" value="{{ old('monthly_amount', $vendor->monthly_amount) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control">{{ old('notes', $vendor->notes) }}</textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
