@extends('admin.layout')

@section('title', 'Manage Vehicles')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Vehicles</h1>
            <p class="text-muted mb-3">List and manage fleet vehicles.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-truck text-primary"></i>
                 </div>
                 <div class="label">Total Vehicles</div>
                 <div class="value text-white">{{ $vehicles->count() }}</div>
             </div>
                 <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-check-circle text-success"></i>
                 </div>
                 <div class="label">Available</div>
                 <div class="value text-white">{{ ($vehicles instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $vehicles->getCollection()->where('is_available', 1)->count() : $vehicles->where('is_available', 1)->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Fleet Directory</h5>
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Vehicle
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Registration</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Tenant</th>
                                    <th>Available</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $v)
                                <tr>
                                    <td>{{ $v->id }}</td>
                                    <td><strong class="text-white">{{ $v->registration_number }}</strong></td>
                                    <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $v->type }}</span></td>
                                    <td>{{ $v->model_year }}</td>
                                    <td>{{ $v->tenant->company_name ?? '—' }}</td>
                                    <td>
                                        @if($v->is_available)
                                            <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Yes</span>
                                        @else
                                            <span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($v->is_active)
                                            <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                        @else
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.vehicles.show', $v->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.vehicles.edit', $v->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.vehicles.destroy', $v->id) }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center" style="color: #64748b; padding: 2rem;">No vehicles found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
