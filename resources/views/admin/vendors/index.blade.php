@extends('admin.layout')

@section('title', 'Manage Vendors')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Vendors</h1>
            <p class="text-muted mb-3">List and manage vendors and contracts.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-handshake text-primary"></i>
                 </div>
                 <div class="label">Total Vendors</div>
                 <div class="value text-white">{{ collect($vendors)->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Vendor Directory</h5>
                    <a href="{{ route('admin.vendors.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Vendor
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contract</th>
                                    <th>Tenant</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Monthly</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $v)
                                <tr>
                                    <td>{{ $v->id }}</td>
                                    <td><strong class="text-white">{{ $v->vendor_name }}</strong></td>
                                    <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $v->contract_name }}</span></td>
                                    <td>{{ $v->tenant->company_name ?? '—' }}</td>
                                    <td>{{ optional($v->start_date)->format('Y-m-d') }}</td>
                                    <td>{{ optional($v->end_date)->format('Y-m-d') }}</td>
                                    <td>₹{{ number_format($v->monthly_amount, 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.vendors.show', $v->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.vendors.edit', $v->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.vendors.destroy', $v->id) }}" style="display:inline;">
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
                                    <td colspan="8" class="text-center" style="color: #64748b; padding: 2rem;">No vendors found</td>
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
