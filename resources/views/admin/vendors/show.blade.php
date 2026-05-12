@extends('admin.layout')

@section('title', 'Vendor Details')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Vendor: {{ $vendor->vendor_name }}</h1>
            <p class="text-muted mb-2">{{ $vendor->contract_name ?? 'Vendor Details' }}</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span><i class="fas fa-building me-1"></i> {{ $vendor->tenant->company_name ?? '—' }}</span>
                <span><i class="fas fa-truck me-1"></i> {{ collect($vendor->vehicles)->count() }} Vehicles</span>
                <span><i class="fas fa-rupee-sign me-1"></i> {{ $vendor->monthly_amount ?? '—' }}/Mo</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm mb-3" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Vendors
            </a>
            <div class="text-end text-muted" style="font-size: 0.85rem;">
                <div>Start: <strong class="text-white">{{ optional($vendor->start_date)->format('Y-m-d') ?? '—' }}</strong></div>
                <div>End: <strong class="text-white">{{ optional($vendor->end_date)->format('Y-m-d') ?? '—' }}</strong></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                
                <ul class="nav nav-pills mb-4" id="vendorTabs" role="tablist" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#vehicles" type="button" style="color: #cbd5e1; background: transparent;">Assigned Vehicles</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#drivers" type="button" style="color: #cbd5e1; background: transparent;">Assigned Drivers</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#bills" type="button" style="color: #cbd5e1; background: transparent;">Bills</button>
                    </li>
                </ul>

                <style>
                    /* Custom pill active state */
                    .nav-pills .nav-link.active { background: rgba(14, 165, 233, 0.1) !important; color: #38bdf8 !important; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 8px; }
                </style>

                <div class="tab-content" id="vendorTabsContent">
                    <!-- Vehicles Tab -->
                    <div class="tab-pane fade show active" id="vehicles" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Assigned Vehicles</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#assignVehicleModal"><i class="fas fa-plus me-1"></i> Assign Vehicles</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Registration</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendor->vehicles as $v)
                                    <tr>
                                        <td><strong class="text-white">{{ $v->registration_number }}</strong></td>
                                        <td><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $v->type }}</span></td>
                                        <td>
                                            <form action="{{ route('admin.vendors.remove-vehicle', [$vendor->id, $v->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Remove vehicle from vendor?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No vehicles assigned</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Drivers Tab -->
                    <div class="tab-pane fade" id="drivers" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Assigned Drivers</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#assignDriverModal"><i class="fas fa-plus me-1"></i> Assign Drivers</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assignedDrivers as $d)
                                    <tr>
                                        <td><strong class="text-white">{{ $d->name }}</strong></td>
                                        <td>{{ $d->phone }}</td>
                                        <td>
                                            <form action="{{ route('admin.vendors.remove-driver', [$vendor->id, $d->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Remove driver from vendor?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No drivers assigned</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bills Tab -->
                    <div class="tab-pane fade" id="bills" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Billing History</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#billModal"><i class="fas fa-plus me-1"></i> Add Bill</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendor->bills as $b)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($b->billing_date)->format('Y-m-d') }}</td>
                                        <td>{{ $b->invoice_number ?? '-' }}</td>
                                        <td>₹{{ $b->amount }}</td>
                                        <td>
                                            @if($b->status == 'paid')
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Paid</span>
                                            @else
                                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($b->file_path)
                                                <a href="{{ asset('storage/'.$b->file_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 4px;">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No bills recorded</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="assignVehicleModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vendors.assign-vehicles', $vendor->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Assign Vehicles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2">
            <label class="form-label">Select Vehicles</label>
            <select name="vehicle_ids[]" class="form-select" multiple style="height:150px" required>
                @foreach($availableVehicles as $v)
                    <option value="{{ $v->id }}">{{ $v->registration_number }} ({{ $v->type }})</option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Assign</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="assignDriverModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vendors.assign-drivers', $vendor->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Assign Drivers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2">
            <label class="form-label">Select Drivers</label>
            <select name="staff_ids[]" class="form-select" multiple style="height:150px" required>
                @foreach($availableDrivers as $d)
                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->phone }})</option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Assign</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="billModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vendors.add-bill', $vendor->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Billing Date</label><input type="date" name="billing_date" class="form-control" required></div>
        <div class="mb-2"><label>Invoice Number</label><input type="text" name="invoice_number" class="form-control"></div>
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
            </select>
        </div>
        <div class="mb-2"><label>File/Receipt</label><input type="file" name="file" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Save Bill</button></div>
    </form>
  </div>
</div>

@endsection
