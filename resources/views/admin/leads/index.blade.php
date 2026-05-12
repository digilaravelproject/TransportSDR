@extends('admin.layout')

@section('title','Leads Management')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Leads Management</h1>
            <p class="text-muted mb-0">Track and manage potential trip leads.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-bullseye text-primary"></i>
                 </div>
                 <div class="label">Total Leads</div>
                 <div class="value text-white">{{ collect($leads->items())->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-clock text-warning"></i>
                 </div>
                 <div class="label">Pending</div>
                 <div class="value text-white">{{ collect($leads->items())->where('status', 'pending')->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <form class="row g-3" method="GET" action="{{ route('admin.leads.index') }}">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Search by customer name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-sm w-100" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Recent Leads</h5>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Lead #</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Route</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td><strong class="text-white">#{{ $lead->lead_number }}</strong></td>
                                        <td><span class="text-white">{{ $lead->customer_name }}</span></td>
                                        <td><span class="text-muted">{{ $lead->customer_contact }}</span></td>
                                        <td><span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">{{ $lead->trip_route }}</span></td>
                                        <td>{{ optional($lead->trip_date)->format('d M Y') }}</td>
                                        <td>
                                            @if($lead->status == 'confirmed')
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Confirmed</span>
                                            @elseif($lead->status == 'pending')
                                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Pending</span>
                                            @else
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Delete this lead?')"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center" style="color: #64748b; padding: 2rem;">No leads found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $leads->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
