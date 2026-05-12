@extends('admin.layout')

@section('title', 'Manage Subscriptions')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Subscriptions</h1>
            <p class="text-muted mb-0">View and manage customer subscriptions.</p>
        </div>
        <div class="cards-col">
            <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-check-circle text-success"></i>
                 </div>
                 <div class="label">Active</div>
                 <div class="value text-white">{{ $stats['active'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-times-circle text-danger"></i>
                 </div>
                 <div class="label">Expired</div>
                 <div class="value text-white">{{ $stats['expired'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-rupee-sign text-info"></i>
                 </div>
                 <div class="label">Revenue</div>
                 <div class="value text-white">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Search by name/email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="payment_status" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Payment Statuses</option>
                            <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;"><i class="fas fa-search me-1"></i> Filter</button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">Clear</a>
                    </div>
                </form>
            </div>

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none;">
                    <h5 class="mb-0 text-white fw-bold">Subscriptions</h5>
                    <div>
                        <a href="{{ route('admin.subscriptions.export') }}" class="btn btn-sm me-2" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.2); border-radius: 6px;">
                            <i class="fas fa-file-excel me-2"></i>Export CSV
                        </a>
                        <a href="{{ route('admin.subscriptions.statistics') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                            <i class="fas fa-chart-pie me-2"></i>Statistics
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User Name</th>
                                    <th>Plan</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->id }}</td>
                                    <td>
                                        <strong>{{ $subscription->user ? $subscription->user->name : 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $subscription->plan ? $subscription->plan->name : 'N/A' }}</span>
                                    </td>
                                    <td>{{ $subscription->user ? $subscription->user->email : 'N/A' }}</td>
                                    <td><strong>₹{{ $subscription->total_amount }}</strong></td>
                                    <td>{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        @if($subscription->status == 'active')
                                            <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                        @elseif($subscription->status == 'expired' || $subscription->status == 'cancelled')
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">{{ ucfirst($subscription->status) }}</span>
                                        @else
                                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">{{ ucfirst($subscription->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="btn btn-sm me-1" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-sm me-1" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center" style="color: #64748b;">No subscriptions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $subscriptions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
