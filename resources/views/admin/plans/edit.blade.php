@extends('admin.layout')

@section('title', 'Edit Plan')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Edit Plan: {{ $plan->name }}</h2>
        <p class="text-muted">Update plan details</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Whoops!</strong> There were some problems with your input.
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (RS) *</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plan->price) }}" step="0.01" required>
                                @error('price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration *</label>
                                <select class="form-select @error('duration') is-invalid @enderror" id="duration" name="duration" required>
                                    <option value="">Select Duration</option>
                                    <option value="monthly" @selected(old('duration', $plan->duration) === 'monthly')>Monthly</option>
                                    <option value="yearly" @selected(old('duration', $plan->duration) === 'yearly')>Yearly</option>
                                    <option value="lifetime" @selected(old('duration', $plan->duration) === 'lifetime')>Lifetime</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="billing_cycle_days" class="form-label">Billing Cycle (Days) *</label>
                        <input type="number" class="form-control @error('billing_cycle_days') is-invalid @enderror" id="billing_cycle_days" name="billing_cycle_days" value="{{ old('billing_cycle_days', $plan->billing_cycle_days) }}" min="1" required>
                        @error('billing_cycle_days')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_vehicles" class="form-label">Max Vehicles (Leave empty for unlimited)</label>
                                <input type="number" class="form-control @error('max_vehicles') is-invalid @enderror" id="max_vehicles" name="max_vehicles" value="{{ old('max_vehicles', $plan->max_vehicles) }}" min="1">
                                @error('max_vehicles')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_trips_per_month" class="form-label">Max Trips/Month (Leave empty for unlimited)</label>
                                <input type="number" class="form-control @error('max_trips_per_month') is-invalid @enderror" id="max_trips_per_month" name="max_trips_per_month" value="{{ old('max_trips_per_month', $plan->max_trips_per_month) }}" min="1">
                                @error('max_trips_per_month')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_staff" class="form-label">Max Staff (Leave empty for unlimited)</label>
                        <input type="number" class="form-control @error('max_staff') is-invalid @enderror" id="max_staff" name="max_staff" value="{{ old('max_staff', $plan->max_staff) }}" min="1">
                        @error('max_staff')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="active" @selected(old('status', $plan->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $plan->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label for="module_access" class="form-label">Module Access</label>
                        <select name="module_access[]" id="module_access" class="form-select" multiple>
                            @foreach($modules as $module)
                                <option value="{{ $module->name }}" @if(collect(old('module_access', $plan->module_access_array))->contains($module->name)) selected @endif>{{ $module->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple modules</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Features (Comma separated)</label>
                        <textarea id="features_text" class="form-control" rows="4" placeholder="Email Support, Mobile App, Basic Tracking">{{ implode(', ', $plan->features ?? []) }}</textarea>
                        <input type="hidden" id="features" name="features[]">
                        <small class="text-muted">Enter features separated by commas</small>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const featuresText = document.getElementById('features_text').value;
    if (featuresText.trim()) {
        const features = featuresText.split(',').map(f => f.trim()).filter(f => f);
        const container = document.getElementById('features');
        container.value = JSON.stringify(features);
    }
});
</script>
@endsection
