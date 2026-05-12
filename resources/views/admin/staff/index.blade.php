@extends('admin.layout')

@section('title', 'Staff Management')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Staff Directory</h1>
            <p class="text-muted mb-0">Manage employees, drivers, and other staff members.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-users text-primary"></i>
                 </div>
                 <div class="label">Total Staff</div>
                 <div class="value text-white">{{ collect($staff->items())->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-check-circle text-success"></i>
                 </div>
                 <div class="label">Active</div>
                 <div class="value text-white">{{ collect($staff->items())->where('is_active', 1)->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            
            @include('admin.partials.alerts')

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">All Staff</h5>
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add New Staff
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Name / Details</th>
                                    <th>Role & Shift</th>
                                    <th>Basic Salary</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staff as $member)
                                    <tr>
                                        <td>
                                            <strong class="text-white">{{ $member->name }}</strong><br>
                                            <small class="text-muted" style="font-size: 0.85rem;"><i class="fas fa-phone-alt me-1"></i>{{ $member->phone }}</small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $member->role->name ?? 'N/A' }}</span><br>
                                            <small class="text-muted" style="font-size: 0.85rem;">{{ $member->shift->name ?? 'No Shift' }}</small>
                                        </td>
                                        <td>₹{{ number_format($member->basic_salary, 2) }}</td>
                                        <td>
                                            <form action="{{ route('admin.staff.toggle-status', $member->id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                @if($member->is_active)
                                                    <button class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b; cursor: pointer;">Active</button>
                                                @else
                                                    <button class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); cursor: pointer;">Suspended</button>
                                                @endif
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.staff.show', $member->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="View Profile">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center" style="color: #64748b; padding: 2rem;">No staff found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">{{ $staff->links('pagination::bootstrap-5') }}</div>
            
        </div>
    </div>
</div>
@endsection
