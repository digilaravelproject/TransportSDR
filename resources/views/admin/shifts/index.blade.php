@extends('admin.layout')

@section('title', 'Manage Shifts')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Shifts</h1>
            <p class="text-muted mb-3">Create and manage work shifts.</p>
        </div>
        <div class="cards-col" style="flex: 3; display: flex; gap: 16px; flex-wrap: wrap;">
            <div class="stat-card" style="flex: 1; min-width: 140px;">
                <div class="stat-pill"><i class="fas fa-clock text-primary"></i></div>
                <div class="label mt-2">Total</div>
                <div class="value text-white">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-card" style="flex: 1; min-width: 140px;">
                <div class="stat-pill"><i class="fas fa-check text-success"></i></div>
                <div class="label mt-2">Active</div>
                <div class="value text-white">{{ $stats['active'] }}</div>
            </div>
            <div class="stat-card" style="flex: 1; min-width: 140px;">
                <div class="stat-pill"><i class="fas fa-moon text-secondary"></i></div>
                <div class="label mt-2">Night</div>
                <div class="value text-white">{{ $stats['night'] }}</div>
            </div>
            <div class="stat-card" style="flex: 1; min-width: 140px;">
                <div class="stat-pill"><i class="fas fa-exclamation-triangle text-warning"></i></div>
                <div class="label mt-2">Overtime</div>
                <div class="value text-white">{{ $stats['overtime'] }}</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            
            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.2);">
                    {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
                </div>
            @endif

            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Types</option>
                            <option value="regular" @selected($type === 'regular')>Regular</option>
                            <option value="overtime" @selected($type === 'overtime')>Overtime</option>
                            <option value="night" @selected($type === 'night')>Night</option>
                            <option value="custom" @selected($type === 'custom')>Custom</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Status</option>
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Search by name or description" value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm w-100 h-100" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none;">
                    <h5 class="mb-0 text-white fw-bold">Shifts Directory</h5>
                    <a href="{{ route('admin.shifts.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Create New Shift
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Shift Name</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="font-weight-medium text-white">{{ $shift->name }}</div>
                                                <div class="text-muted" style="font-size: 0.8rem;">Date: {{ $shift->date ? $shift->date->format('Y-m-d') : 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2);">{{ $shift->time_range }}</span>
                                        </td>
                                        <td>
                                            @if($shift->type === 'regular')
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Regular</span>
                                            @elseif($shift->type === 'overtime')
                                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Overtime</span>
                                            @elseif($shift->type === 'night')
                                                <span class="badge" style="background: rgba(168, 85, 247, 0.1); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.2);">Night</span>
                                            @else
                                                <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">Custom</span>
                                            @endif
                                        </td>
                                        <td>{{ $shift->date ? $shift->date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @if($shift->is_active)
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                            @else
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.shifts.show', $shift->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.shifts.destroy', $shift->id) }}" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="color: #64748b; padding: 2rem;">
                                            No shifts found. <a href="{{ route('admin.shifts.create') }}" style="color: #38bdf8; text-decoration: underline;">Create one</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($shifts->hasPages())
                        <div class="mt-4">
                            {{ $shifts->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
