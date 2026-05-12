@extends('admin.layout')

@section('title', 'Manage Plans')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Plans</h1>
            <p class="text-muted mb-0">Subscription plans and feature sets</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-layer-group text-primary"></i>
                 </div>
                 <div class="label">Total Plans</div>
                 <div class="value text-white">{{ collect($plans->items())->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-4" style="background: transparent; border: none;">
                    <h5 class="mb-0 text-white fw-bold">All Plans</h5>
                    <a href="{{ route('admin.plans.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Plan
                    </a>
                </div>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.2);">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
                    </div>
                @endif

                <div class="row g-4">
                    @forelse($plans as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0" style="background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); box-shadow: 0 8px 24px rgba(2,6,23,0.8); border-radius: 16px; overflow: hidden; position: relative;">
                            
                            @if($plan->status == 'active')
                                <div style="position: absolute; top: 15px; right: 15px;">
                                    <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b; border-radius: 8px; padding: 6px 12px; font-weight: 600; letter-spacing: 0.5px;">ACTIVE</span>
                                </div>
                            @else
                                <div style="position: absolute; top: 15px; right: 15px;">
                                    <span class="badge" style="background: #2a2a2a; color: #9ca3af; border: 1px solid #3d3d3d; border-radius: 8px; padding: 6px 12px; font-weight: 600; letter-spacing: 0.5px;">INACTIVE</span>
                                </div>
                            @endif

                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-1" style="color: #f8fafc; font-size: 1.25rem;">{{ $plan->name }}</h4>
                                <p class="mb-4" style="color: #94a3b8; font-size: 0.85rem; min-height: 40px;">{{ $plan->description }}</p>
                                
                                <div class="mb-4">
                                    <span style="font-size: 2rem; font-weight: 700; color: #e2e8f0;">₹{{ number_format($plan->price, 2) }}</span>
                                    <span style="color: #64748b; font-weight: 500;">/ {{ ucfirst($plan->duration) }}</span>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.02);">
                                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Vehicles</div>
                                            <div style="font-size: 1.1rem; font-weight: 600; color: #f1f5f9;">{{ $plan->max_vehicles ?? 'Unlimited' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.02);">
                                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Trips/Mo</div>
                                            <div style="font-size: 1.1rem; font-weight: 600; color: #f1f5f9;">{{ $plan->max_trips_per_month ?? 'Unlimited' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.02);">
                                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Staff</div>
                                            <div style="font-size: 1.1rem; font-weight: 600; color: #f1f5f9;">{{ $plan->max_staff ?? 'Unlimited' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.02);">
                                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Billing</div>
                                            <div style="font-size: 1.1rem; font-weight: 600; color: #f1f5f9;">{{ $plan->billing_cycle_days }}d</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 style="color: #cbd5e1; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px; margin-bottom: 12px;">Modules & Features</h6>
                                    <ul class="list-unstyled" style="font-size: 0.9rem; color: #94a3b8;">
                                        @forelse($plan->module_access_array as $module)
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="fas fa-check-circle me-2" style="color: #0ea5e9; font-size: 0.8rem;"></i>
                                                {{ $module }}
                                            </li>
                                        @empty
                                            <li class="text-muted">No modules assigned</li>
                                        @endforelse
                                        @if($plan->features)
                                            @foreach($plan->features as $feature)
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="fas fa-check-circle me-2" style="color: #10b981; font-size: 0.8rem;"></i>
                                                {{ $feature }}
                                            </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-footer p-3" style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small style="color: #64748b; font-size: 0.75rem;">Created {{ $plan->created_at->format('M d, Y') }}</small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.plans.show', $plan->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 12px; padding: 16px;">
                            <i class="fas fa-info-circle me-2"></i>
                            No plans found. <a href="{{ route('admin.plans.create') }}" style="color: #bae6fd; text-decoration: underline;">Create one</a>
                        </div>
                    </div>
                    @endforelse
                </div>

                @if($plans->hasPages())
                <nav aria-label="Page navigation" class="mt-4">
                    {{ $plans->links('pagination::bootstrap-4') }}
                </nav>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection