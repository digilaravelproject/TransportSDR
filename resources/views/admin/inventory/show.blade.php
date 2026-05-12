@extends('admin.layout')

@section('title','Inventory Detail')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Inventory: <span style="color: #38bdf8;">{{ $inventory->name }}</span></h1>
            <p class="text-muted mb-2">View item details and recent stock activity.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span><i class="fas fa-barcode me-1"></i> SKU: {{ $inventory->item_code }}</span>
                <span>
                    <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $inventory->category }}</span>
                </span>
                <span><i class="fas fa-map-marker-alt me-1"></i> {{ $inventory->storage_location ?: 'N/A' }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm mb-3" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Inventory
            </a>
            <div class="text-end text-muted" style="font-size: 0.85rem;">
                <div>Unit Price: <strong class="text-white fs-5">₹{{ number_format($inventory->unit_price, 2) }}</strong></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <!-- Left Column: Details -->
        <div class="col-md-5">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Item Specification</h5>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 text-center" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Current Stock</p>
                            <p class="text-white mb-0 fs-4 fw-bold {{ $inventory->quantity_in_stock <= $inventory->reorder_level ? 'text-danger' : 'text-success' }}">{{ $inventory->quantity_in_stock }}</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 text-center" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Reorder Level</p>
                            <p class="text-white mb-0 fs-4 fw-bold text-warning">{{ $inventory->reorder_level }}</p>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <p class="text-muted mb-1" style="font-size: 0.85rem;">Description</p>
                        <p class="text-white mb-0" style="font-size: 0.95rem; line-height: 1.5;">{{ $inventory->description ?: 'No description available for this item.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Activity -->
        <div class="col-md-7">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h5 class="fw-bold text-white mb-0">Recent Activity</h5>
                </div>
                
                <ul class="list-group list-group-flush">
                    @forelse($inventory->stocks()->latest()->take(10)->get() as $s)
                        <li class="list-group-item bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if(strtolower($s->transaction_type) == 'in')
                                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;"><i class="fas fa-arrow-down me-1"></i> In</span>
                                    @else
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2)"><i class="fas fa-arrow-up me-1"></i> Out</span>
                                    @endif
                                    <span class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($s->transaction_date)->format('d M Y') }}</span>
                                </div>
                                <div class="text-white mt-1">{{ $s->reason ?: 'Stock updated' }}</div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-5 {{ strtolower($s->transaction_type) == 'in' ? 'text-success' : 'text-danger' }}">
                                    {{ strtolower($s->transaction_type) == 'in' ? '+' : '-' }}{{ $s->quantity }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent border-0 text-muted px-0 text-center py-4">No recent stock activity found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
