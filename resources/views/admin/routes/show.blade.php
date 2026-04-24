@extends('admin.layout')

@section('title', 'Route Details')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Route Details</h2>
        <p class="text-muted">Route information and assigned vehicles</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Route Information -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="35%">Name</th>
                        <td>{{ $route->name }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if ($route->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Origin</th>
                        <td>{{ $route->origin }}</td>
                    </tr>
                    <tr>
                        <th>Destination</th>
                        <td>{{ $route->destination }}</td>
                    </tr>
                    <tr>
                        <th>Distance</th>
                        <td>{{ $route->distance ? $route->distance . ' km' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Estimated Time</th>
                        <td>{{ $route->estimated_time ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Stops</th>
                        <td>
                            @php $stops = is_array($route->stops) ? $route->stops : json_decode($route->stops, true) @endphp
                            @if ($stops && count($stops) > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach ($stops as $stop)
                                        @if($stop)
                                            <li>{{ $stop }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No stops defined</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Vehicles -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i>Assigned Vehicles</h5>
                <span class="badge bg-primary rounded-pill">{{ $route->vehicles->count() }}</span>
            </div>
            <div class="card-body">
                
                <!-- Assignment Form -->
                <form action="{{ route('admin.routes.add-vehicle', $route->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="input-group">
                        <select name="vehicle_id" class="form-select border-primary" required>
                            <option value="">-- Select Vehicle to Assign --</option>
                            @foreach ($availableVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->type }} (Cap: {{ $vehicle->seating_capacity }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary px-4">
                            Assign
                        </button>
                    </div>
                </form>

                <!-- List of vehicles -->
                @if ($route->vehicles->count() > 0)
                    <div class="list-group">
                        @foreach ($route->vehicles as $vehicle)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $vehicle->registration_number }}</h6>
                                    <small class="text-muted">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->type }})</small>
                                </div>
                                <form action="{{ route('admin.routes.remove-vehicle', $route->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this vehicle from the route?');">
                                    @csrf
                                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Vehicle">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning mb-0 text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i> No vehicles assigned to this route yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
