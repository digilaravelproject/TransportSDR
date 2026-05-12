@extends('admin.layout')

@section('title', 'Manage Trips')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Trips</h1>
            <p class="text-muted mb-3">Overview of all active, scheduled, and completed trips.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-car text-primary"></i>
                 </div>
                 <div class="label">Total Trips</div>
                 <div class="value text-white">{{ collect($trips)->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-spinner text-warning"></i>
                 </div>
                 <div class="label">Ongoing</div>
                 <div class="value text-white">{{ collect($trips)->where('status', 'ongoing')->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-check-circle text-success"></i>
                 </div>
                 <div class="label">Completed</div>
                 <div class="value text-white">{{ collect($trips)->where('status', 'completed')->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">All Trips</h5>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Trip Number</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Route</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips as $trip)
                                <tr>
                                    <td><strong class="text-white">{{ $trip->trip_number }}</strong></td>
                                    <td>
                                        <div class="text-white">{{ $trip->customer_name }}</div>
                                        <div class="text-muted" style="font-size: 0.85rem;">{{ $trip->customer_phone }}</div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('d M Y') }}</td>
                                    <td><span class="text-muted">{{ $trip->pickup_address ?? '-' }}</span> <i class="fas fa-arrow-right mx-1" style="font-size:10px;"></i> <span class="text-white">@if(!empty($trip->destination_points)) {{ collect($trip->destination_points)->pluck('name')->implode(', ') }} @else - @endif</span></td>
                                    <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $trip->vehicle->registration_number ?? '-' }}</span></td>
                                    <td>{{ $trip->driver->name ?? '-' }}</td>
                                    <td>
                                        @if($trip->status == 'completed')
                                            <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Completed</span>
                                        @elseif($trip->status == 'ongoing')
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2);">Ongoing</span>
                                        @elseif($trip->status == 'scheduled')
                                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Scheduled</span>
                                        @else
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">{{ ucfirst($trip->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="View Trip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.trips.destroy', $trip->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure you want to delete this trip?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center" style="color: #64748b; padding: 2rem;">No trips found.</td>
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
