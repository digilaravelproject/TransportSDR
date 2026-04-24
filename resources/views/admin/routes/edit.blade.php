@extends('admin.layout')

@section('title', 'Edit Route')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Edit Route</h2>
        <p class="text-muted">Modify route details, stops and schedules</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.routes.update', $route->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Route Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $route->name) }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" {{ (old('status', $route->status) == 'active') ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ (old('status', $route->status) == 'inactive') ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="origin" class="form-label">Origin <span class="text-danger">*</span></label>
                    <input type="text" name="origin" id="origin" class="form-control" value="{{ old('origin', $route->origin) }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="destination" class="form-label">Destination <span class="text-danger">*</span></label>
                    <input type="text" name="destination" id="destination" class="form-control" value="{{ old('destination', $route->destination) }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="distance" class="form-label">Distance (km)</label>
                    <input type="number" step="0.01" name="distance" id="distance" class="form-control" value="{{ old('distance', $route->distance) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="estimated_time" class="form-label">Estimated Time</label>
                    <input type="text" name="estimated_time" id="estimated_time" class="form-control" value="{{ old('estimated_time', $route->estimated_time) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Stops</label>
                <div id="stops-container">
                    @php $stops = old('stops', $route->stops ?? []) @endphp
                    @if (is_array($stops) && count($stops) > 0)
                        @foreach ($stops as $stop)
                            <div class="input-group mb-2 stop-entry">
                                <input type="text" name="stops[]" class="form-control" value="{{ $stop }}" placeholder="Stop name">
                                <button type="button" class="btn btn-outline-danger remove-stop"><i class="fas fa-times"></i></button>
                            </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2 stop-entry">
                            <input type="text" name="stops[]" class="form-control" placeholder="Stop name">
                            <button type="button" class="btn btn-outline-danger remove-stop"><i class="fas fa-times"></i></button>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-stop">
                    <i class="fas fa-plus me-1"></i> Add Stop
                </button>
            </div>
            
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-warning text-dark">
                    <i class="fas fa-sync me-2"></i> Update Route
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('add-stop').addEventListener('click', function() {
        const container = document.getElementById('stops-container');
        const entry = document.createElement('div');
        entry.className = 'input-group mb-2 stop-entry';
        entry.innerHTML = `
            <input type="text" name="stops[]" class="form-control" placeholder="Stop name">
            <button type="button" class="btn btn-outline-danger remove-stop"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(entry);
    });

    document.getElementById('stops-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-stop')) {
            e.target.closest('.stop-entry').remove();
        }
    });
</script>
@endsection
