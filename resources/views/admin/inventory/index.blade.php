@extends('admin.layout')

@section('title','Inventory Management')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Inventory Management</h1>
            <p class="text-muted mb-0">Track and manage inventory stock and assets.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-boxes text-primary"></i>
                 </div>
                 <div class="label">Total Items</div>
                 <div class="value text-white">{{ collect($inventories->items())->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-cubes text-warning"></i>
                 </div>
                 <div class="label">Total Stock</div>
                 <div class="value text-white">{{ collect($inventories->items())->sum('quantity_in_stock') }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Inventory Directory</h5>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventories as $inv)
                                    <tr>
                                        <td><strong class="text-white">{{ $inv->name }}</strong></td>
                                        <td><span class="text-muted">{{ $inv->item_code }}</span></td>
                                        <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $inv->category }}</span></td>
                                        <td><strong class="text-white">{{ $inv->quantity_in_stock }}</strong></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.inventory.show', $inv) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" action="{{ route('admin.inventory.destroy', $inv) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center" style="color: #64748b; padding: 2rem;">No inventory items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $inventories->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
