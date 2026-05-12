@extends('admin.layout')

@section('title', 'Vehicle Details')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">{{ $vehicle->registration_number }} — {{ $vehicle->type }}</h1>
            <p class="text-muted mb-2">View vehicle information and history.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span><i class="fas fa-building me-1"></i> {{ $vehicle->tenant->company_name ?? 'Super Admin' }}</span>
                <span><i class="fas fa-calendar-alt me-1"></i> {{ $vehicle->model_year ?? '—' }}</span>
                <span><i class="fas fa-users me-1"></i> {{ $vehicle->seating_capacity ?? '—' }} Seats</span>
                <span><i class="fas fa-rupee-sign me-1"></i> {{ $vehicle->per_km_price ?? '—' }}/KM</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm mb-3" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Fleet
            </a>
            <div class="text-end text-muted" style="font-size: 0.85rem;">
                <div>RC: <strong class="text-white">{{ $vehicle->rc_number ?? '—' }}</strong></div>
                <div>INS: <strong class="text-white">{{ $vehicle->insurance_number ?? '—' }}</strong></div>
                <div>PRMT: <strong class="text-white">{{ $vehicle->permit_number ?? '—' }}</strong></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                
                <ul class="nav nav-pills mb-4" id="vehicleTabs" role="tablist" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#fuel" type="button" style="color: #cbd5e1; background: transparent;">Fuel Logs</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#service" type="button" style="color: #cbd5e1; background: transparent;">Service</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#repair" type="button" style="color: #cbd5e1; background: transparent;">Repair</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#docs" type="button" style="color: #cbd5e1; background: transparent;">Documents</button>
                    </li>
                </ul>

                <style>
                    /* Custom pill active state */
                    .nav-pills .nav-link.active { background: rgba(14, 165, 233, 0.1) !important; color: #38bdf8 !important; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 8px; }
                </style>

                <div class="tab-content" id="vehicleTabsContent">
                    <!-- Fuel Tab -->
                    <div class="tab-pane fade show active" id="fuel" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Fuel Logs</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#fuelModal"><i class="fas fa-plus me-1"></i> Add Fuel</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Station</th>
                                        <th>Quantity (L)</th>
                                        <th>Price/L</th>
                                        <th>Amount</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities->where('activity_type', 'fuel') as $f)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($f->activity_date)->format('Y-m-d') }}</td>
                                        <td>{{ $f->station_name ?? '-' }}</td>
                                        <td>{{ $f->quantity }}</td>
                                        <td>{{ $f->price_per_unit }}</td>
                                        <td>₹{{ $f->amount }}</td>
                                        <td>
                                            @if($f->receipt_path)
                                                <a href="{{ asset('storage/'.$f->receipt_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 4px;">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No fuel logs found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Service Tab -->
                    <div class="tab-pane fade" id="service" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Service History</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#serviceModal"><i class="fas fa-plus me-1"></i> Add Service</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Workshop</th>
                                        <th>KM Reading</th>
                                        <th>Amount</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities->where('activity_type', 'service') as $s)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($s->activity_date)->format('Y-m-d') }}</td>
                                        <td>{{ $s->title }}</td>
                                        <td>{{ $s->workshop_name ?? '-' }}</td>
                                        <td>{{ $s->km_reading ?? '-' }}</td>
                                        <td>₹{{ $s->amount }}</td>
                                        <td>
                                            @if($s->receipt_path)
                                                <a href="{{ asset('storage/'.$s->receipt_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 4px;">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No service records found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Repair Tab -->
                    <div class="tab-pane fade" id="repair" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Repair History</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#repairModal"><i class="fas fa-plus me-1"></i> Add Repair</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Garage</th>
                                        <th>Amount</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities->where('activity_type', 'repair') as $r)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($r->activity_date)->format('Y-m-d') }}</td>
                                        <td>{{ $r->title }}</td>
                                        <td>{{ $r->garage_name ?? '-' }}</td>
                                        <td>₹{{ $r->amount }}</td>
                                        <td>
                                            @if($r->receipt_path)
                                                <a href="{{ asset('storage/'.$r->receipt_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 4px;">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No repair records found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Docs Tab -->
                    <div class="tab-pane fade" id="docs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Documents</h6>
                            <button type="button" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#docsModal"><i class="fas fa-upload me-1"></i> Upload Document</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Number</th>
                                        <th>Issue Date</th>
                                        <th>Expiry Date</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $d)
                                    <tr>
                                        <td>{{ $d->document_type }}</td>
                                        <td>{{ $d->document_number ?? '-' }}</td>
                                        <td>{{ $d->issue_date ? \Carbon\Carbon::parse($d->issue_date)->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $d->expiry_date ? \Carbon\Carbon::parse($d->expiry_date)->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            @if($d->document_path)
                                                <a href="{{ asset('storage/'.$d->document_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 4px;">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No documents found</td></tr>
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
<div class="modal fade" id="fuelModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vehicles.store-fuel', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Fuel Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Date</label><input type="date" name="activity_date" class="form-control" required></div>
        <div class="mb-2"><label>Quantity</label><input type="number" step="0.01" name="quantity" class="form-control" required></div>
        <div class="mb-2"><label>Price Per Ltr</label><input type="number" step="0.01" name="price_per_unit" class="form-control" required></div>
        <div class="mb-2"><label>Total Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Station Name</label><input type="text" name="station_name" class="form-control"></div>
        <div class="mb-2"><label>Receipt File</label><input type="file" name="receipt" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Save</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vehicles.store-service', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Service Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Date</label><input type="date" name="activity_date" class="form-control" required></div>
        <div class="mb-2"><label>Title</label><input type="text" name="title" class="form-control" required></div>
        <div class="mb-2"><label>Workshop Name</label><input type="text" name="workshop_name" class="form-control"></div>
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Amount Paid</label><input type="number" step="0.01" name="amount_paid" class="form-control"></div>
        <div class="mb-2"><label>KM Reading</label><input type="number" name="km_reading" class="form-control"></div>
        <div class="mb-2"><label>Receipt File</label><input type="file" name="receipt" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Save</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="repairModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vehicles.store-repair', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Repair Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Date</label><input type="date" name="activity_date" class="form-control" required></div>
        <div class="mb-2"><label>Title</label><input type="text" name="title" class="form-control" required></div>
        <div class="mb-2"><label>Garage Name</label><input type="text" name="garage_name" class="form-control"></div>
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Receipt File</label><input type="file" name="receipt" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Save</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="docsModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.vehicles.upload-document', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Upload Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Type</label><input type="text" name="document_type" class="form-control" required></div>
        <div class="mb-2"><label>Number</label><input type="text" name="document_number" class="form-control"></div>
        <div class="mb-2"><label>Issue Date</label><input type="date" name="issue_date" class="form-control"></div>
        <div class="mb-2"><label>Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
        <div class="mb-2"><label>File</label><input type="file" name="file" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Upload</button></div>
    </form>
  </div>
</div>

@endsection
