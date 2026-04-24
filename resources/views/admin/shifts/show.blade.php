@extends('admin.layout')

@section('title', $shift->name)

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">{{ $shift->name }}</h2>
        <p class="text-muted">Date: {{ $shift->date ? $shift->date->format('Y-m-d') : 'N/A' }}</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Shift Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Time Range</h6>
                        <p class="h5">{{ $shift->time_range }}</p>
                    </div>
                    <?php /*<div class="col-md-6">
                        <h6 class="text-muted">Duration</h6>
                        <p class="h5">{{ $shift->calculateDuration() }} Hours</p>
                    </div> */?>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Type</h6>
                        @if($shift->type === 'regular')
                            <span class="badge bg-success">Regular</span>
                        @elseif($shift->type === 'overtime')
                            <span class="badge bg-warning">Overtime</span>
                        @elseif($shift->type === 'night')
                            <span class="badge bg-danger">Night</span>
                        @else
                            <span class="badge bg-info">Custom</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        @if($shift->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Removed days, max_drivers, hourly_rate section -->

                <hr>

                @if($shift->notes)
                    <h6 class="text-muted">Notes</h6>
                    <p>{{ $shift->notes }}</p>
                @endif

                <div class="text-muted small">
                    <p><strong>Created:</strong> {{ $shift->created_at->format('d M Y, H:i') }}</p>
                    <p><strong>Updated:</strong> {{ $shift->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Assigned Drivers Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0">Assigned Drivers</h5>
                <span class="text-warning fw-bold">{{ $shift->drivers->count() }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($shift->drivers as $driver)
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 text-warning">
                                    <i class="far fa-user fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $driver->name }}</h6>
                                    <small class="text-muted">Driver</small>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.shifts.remove-driver', $shift->id) }}">
                                @csrf
                                <input type="hidden" name="driver_id" value="{{ $driver->id }}">
                                <button type="submit" class="btn btn-link text-danger p-0" title="Remove" onclick="return confirm('Remove driver?');">
                                    <i class="far fa-times-circle fa-lg"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-4">No drivers assigned yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                <form method="POST" action="{{ route('admin.shifts.add-driver', $shift->id) }}">
                    @csrf
                    <div class="row align-items-center mb-4">
                        <div class="col">
                            <h2 class="fw-bold mb-0">{{ $shift->name }}</h2>
                            <p class="text-muted">Shift overview and assigned drivers</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                                @if(!$shift->drivers->contains('id', $dr->id))
                                    <option value="{{ $dr->id }}">{{ $dr->name }} ({{ $dr->phone }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn w-100" style="background-color: #f97316; color: white;">
                        Assign Drivers
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i> Edit Shift
                </a>
                <form method="POST" action="{{ route('admin.shifts.destroy', $shift->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this shift?');">
                        <i class="fas fa-trash me-2"></i> Delete Shift
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Shifts
    </a>
</div>
@endsection
