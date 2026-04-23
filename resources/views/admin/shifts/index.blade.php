@extends('admin.layout')

@section('title', 'Manage Shifts')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Shifts</h2>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New Shift
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Shifts</h6>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Active</h6>
                    <h3>{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Inactive</h6>
                    <h3>{{ $stats['inactive'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Regular</h6>
                    <h3>{{ $stats['regular'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Overtime</h6>
                    <h3>{{ $stats['overtime'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title">Night</h6>
                    <h3>{{ $stats['night'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="regular" @selected($type === 'regular')>Regular</option>
                        <option value="overtime" @selected($type === 'overtime')>Overtime</option>
                        <option value="night" @selected($type === 'night')>Night</option>
                        <option value="custom" @selected($type === 'custom')>Custom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or description" value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Shifts Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>Shift Name</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Max Drivers</th>
                        <th>Hourly Rate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>
                                <div>
                                    <div class="font-weight-medium">{{ $shift->name }}</div>
                                    <div class="text-muted font-size-sm">{{ $shift->description ? Str::limit($shift->description, 40) : 'No description' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-blue">{{ $shift->time_range }}</span>
                            </td>
                            <td>
                                @if($shift->type === 'regular')
                                    <span class="badge bg-success">Regular</span>
                                @elseif($shift->type === 'overtime')
                                    <span class="badge bg-warning">Overtime</span>
                                @elseif($shift->type === 'night')
                                    <span class="badge bg-danger">Night</span>
                                @else
                                    <span class="badge bg-info">Custom</span>
                                @endif
                            </td>
                            <td>{{ $shift->calculateDuration() }} hrs</td>
                            <td>
                                @if($shift->max_drivers)
                                    <span class="badge bg-secondary">{{ $shift->max_drivers }}</span>
                                @else
                                    <span class="badge bg-light">Unlimited</span>
                                @endif
                            </td>
                            <td>
                                @if($shift->hourly_rate)
                                    RS. {{ number_format($shift->hourly_rate, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($shift->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.shifts.show', $shift->id) }}" class="btn btn-sm btn-icon btn-ghost-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-sm btn-icon btn-ghost-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.shifts.destroy', $shift->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-ghost-danger" onclick="return confirm('Are you sure?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No shifts found. <a href="{{ route('admin.shifts.create') }}">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($shifts->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $shifts->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
