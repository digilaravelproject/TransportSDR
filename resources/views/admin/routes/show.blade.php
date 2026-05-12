@extends('admin.layout')

@section('title', 'Route Details')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Route: <span style="color: #38bdf8;">{{ $route->name }}</span></h1>
            <p class="text-muted mb-2">View route details and manage assigned vehicles.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span>
                    @if ($route->status === 'active')
                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                    @endif
                </span>
                <span><i class="fas fa-map-marker-alt me-1"></i> Origin: {{ $route->origin }}</span>
                <span><i class="fas fa-flag-checkered me-1"></i> Destination: {{ $route->destination }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.routes.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-sm" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 6px;">
                <i class="fas fa-edit me-1"></i> Edit Route
            </a>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <!-- Route Information -->
        <div class="col-md-6">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;"><i class="fas fa-info-circle me-2" style="color: #38bdf8;"></i> Route Information</h5>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Distance</p>
                            <p class="text-white mb-0 fs-5">{{ $route->distance ? $route->distance . ' km' : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Estimated Time</p>
                            <p class="text-white mb-0 fs-5">{{ $route->estimated_time ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <h6 class="text-white mb-3">Waypoints & Stops</h6>
                        <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                            @php $stops = is_array($route->stops) ? $route->stops : json_decode($route->stops, true) @endphp
                            @if ($stops && count($stops) > 0)
                                <ul class="list-unstyled mb-0 route-stops" style="position: relative; padding-left: 20px;">
                                    @foreach ($stops as $index => $stop)
                                        @if($stop)
                                            <li class="mb-3 position-relative text-light" style="padding-left: 15px;">
                                                <span style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: #38bdf8; border: 2px solid #0f172a;"></span>
                                                @if(!$loop->last)
                                                    <span style="position: absolute; left: -16px; top: 14px; width: 2px; height: calc(100% + 10px); background: rgba(56, 189, 248, 0.3);"></span>
                                                @endif
                                                {{ $stop }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No specific stops defined for this route.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Vehicles -->
        <div class="col-md-6">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h5 class="mb-0 text-white fw-bold"><i class="fas fa-truck me-2" style="color: #38bdf8;"></i> Assigned Vehicles</h5>
                    <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $route->vehicles->count() }} Total</span>
                </div>
                
                <!-- Assignment Form -->
                <form action="{{ route('admin.routes.add-vehicle', $route->id) }}" method="POST" class="mb-4 p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    @csrf
                    <label class="form-label text-muted small mb-2">Assign New Vehicle</label>
                    <div class="input-group">
                        <select name="vehicle_id" class="form-select" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #fff;" required>
                            <option value="">-- Select Vehicle to Assign --</option>
                            @foreach ($availableVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->type }} (Cap: {{ $vehicle->seating_capacity }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">
                            Assign
                        </button>
                    </div>
                </form>

                <!-- List of vehicles -->
                @if ($route->vehicles->count() > 0)
                    <div class="list-group">
                        @foreach ($route->vehicles as $vehicle)
                            <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 mb-2 border-bottom border-secondary pb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); color: #94a3b8;">
                                        <i class="fas fa-shuttle-van"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-white">{{ $vehicle->registration_number }}</h6>
                                        <small class="text-muted">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->type }})</small>
                                    </div>
                                </div>
                                <form action="{{ route('admin.routes.remove-vehicle', $route->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this vehicle from the route?');" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                    <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" title="Remove Vehicle">
                                        <i class="fas fa-unlink"></i> Remove
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert mt-3" style="background: rgba(245, 158, 11, 0.1); border: 1px dashed rgba(245, 158, 11, 0.3); color: #fbbf24; text-align: center;">
                        <i class="fas fa-exclamation-triangle me-2"></i> No vehicles assigned to this route yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
