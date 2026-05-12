@extends('admin.layout')

@section('title', 'Vehicle Types')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Vehicle Types</h1>
            <p class="text-muted mb-3">Manage vehicle types used by the platform.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-truck-monster text-primary"></i>
                 </div>
                 <div class="label">Total Types</div>
                 <div class="value text-white">{{ collect($vehicleTypes)->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Vehicle Types Directory</h5>
                    <a href="{{ route('admin.vehicle-types.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Vehicle Type
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Capacity</th>
                                    <th>Price / KM</th>
                                    <th>AC Extra</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicleTypes as $type)
                                    <tr>
                                        <td>{{ $type->id }}</td>
                                        <td><strong class="text-white">{{ $type->name }}</strong></td>
                                        <td><span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">{{ $type->capacity }} Seats</span></td>
                                        <td><strong class="text-white">₹{{ number_format($type->price_per_km, 2) }}</strong></td>
                                        <td>₹{{ number_format($type->ac_extra_price, 2) }}</td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                            @else
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.vehicle-types.show', $type->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.vehicle-types.edit', $type->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.vehicle-types.destroy', $type->id) }}" style="display:inline;">
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
                                        <td colspan="7" class="text-center" style="color: #64748b; padding: 2rem;">No vehicle types found.</td>
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
