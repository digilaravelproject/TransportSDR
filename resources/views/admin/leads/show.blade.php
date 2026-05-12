@extends('admin.layout')

@section('title','Lead #'. $lead->lead_number)

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Lead: {{ $lead->customer_name }} <small class="text-muted fs-5">#{{ $lead->lead_number }}</small></h1>
            <p class="text-muted mb-2">Manage lead details, follow-ups, and convert to trip.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span>
                    @if($lead->status == 'converted')
                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Converted</span>
                    @elseif($lead->status == 'pending')
                        <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Pending</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Cancelled</span>
                    @endif
                </span>
                <span><i class="fas fa-phone me-1"></i> {{ $lead->customer_contact }}</span>
                <span><i class="fas fa-route me-1"></i> {{ $lead->trip_route }}</span>
                <span><i class="fas fa-calendar-alt me-1"></i> {{ optional($lead->trip_date)->format('d M Y') }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <div class="d-flex gap-2 mb-3">
                <a href="{{ route('admin.leads.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                @if($lead->status !== 'converted')
                <form method="POST" action="{{ route('admin.leads.convert-to-trip', $lead) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background: #113627; color: #34d399; border: 1px solid #1a513b; border-radius: 6px;" onclick="return confirm('Are you sure you want to convert this lead to a trip?')">
                        <i class="fas fa-exchange-alt me-1"></i> Convert to Trip
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Lead Details Form -->
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Lead Details</h5>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Customer Name</label>
                            <input name="customer_name" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ $lead->customer_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Contact</label>
                            <input name="customer_contact" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ $lead->customer_contact }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Trip Route</label>
                            <input name="trip_route" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ $lead->trip_route }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Trip Date</label>
                            <input name="trip_date" type="date" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ optional($lead->trip_date)->toDateString() }}">
                        </div>
                        <div class="col-12 mt-3">
                            <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">Update Details</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Notes & Followups Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                        <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                            <h5 class="fw-bold text-white mb-0">Notes</h5>
                            <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#noteModal"><i class="fas fa-plus"></i></button>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($lead->notes as $note)
                                <li class="list-group-item bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-2">
                                    <strong class="text-white">{{ $note->author->name ?? 'Admin' }}</strong>
                                    <div class="small text-muted mb-1">{{ $note->created_at->diffForHumans() }}</div>
                                    <div class="text-light">{{ $note->note }}</div>
                                </li>
                            @empty
                                <li class="list-group-item bg-transparent border-0 text-muted px-0 text-center py-3">No notes available</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                        <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                            <h5 class="fw-bold text-white mb-0">Follow Ups</h5>
                            <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#followupModal"><i class="fas fa-plus"></i></button>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($lead->followups as $f)
                                <li class="list-group-item bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-2">
                                    <strong class="text-white">{{ $f->author->name ?? 'Admin' }}</strong>
                                    <div class="small text-muted mb-1">Followup: {{ \Carbon\Carbon::parse($f->followup_date)->format('d M Y') }}</div>
                                    <div class="text-light">{{ $f->notes }}</div>
                                </li>
                            @empty
                                <li class="list-group-item bg-transparent border-0 text-muted px-0 text-center py-3">No followups available</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Duty Sheets -->
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h5 class="fw-bold text-white mb-0">Duty Sheets</h5>
                    <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#dutySheetModal"><i class="fas fa-upload me-1"></i> Upload</button>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($lead->dutySheets as $ds)
                        <li class="list-group-item bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted mb-1">{{ $ds->created_at->format('d M Y, H:i') }}</div>
                                <div class="text-light">{{ $ds->notes }}</div>
                            </div>
                            <a href="{{ asset('storage/' . $ds->file_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px;">View</a>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent border-0 text-muted px-0 text-center py-3">No duty sheets uploaded</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Assignment -->
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h6 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Assignment</h6>
                
                <form method="POST" action="{{ route('admin.leads.assign-vehicle', $lead) }}" class="mb-3">
                    @csrf
                    <label class="form-label text-muted small mb-1">Vehicle</label>
                    <div class="input-group">
                        <select name="vehicle_id" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">-- Select Vehicle --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ $lead->vehicle_id == $v->id ? 'selected' : '' }}>{{ $v->registration_number }} ({{ $v->type }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">Assign</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.leads.assign-driver', $lead) }}">
                    @csrf
                    <label class="form-label text-muted small mb-1">Driver</label>
                    <div class="input-group">
                        <select name="driver_id" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">-- Select Driver --</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}" {{ $lead->driver_id == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->phone }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">Assign</button>
                    </div>
                </form>
            </div>

            <!-- Expenses -->
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h6 class="fw-bold text-white mb-0">Expenses</h6>
                    <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="fas fa-plus"></i></button>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($lead->expenses as $e)
                        <li class="list-group-item bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-white">{{ $e->expense_type }}</strong>
                                <div class="small text-muted">{{ $e->description }}</div>
                            </div>
                            <div class="text-end">
                                <strong class="text-white">₹{{ $e->amount }}</strong>
                                @if($e->receipt_path)
                                    <br><a href="{{ asset('storage/'.$e->receipt_path) }}" target="_blank" class="small" style="color: #38bdf8;">Receipt</a>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent border-0 text-muted px-0 text-center py-3">No expenses recorded</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="noteModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.leads.add-note', $lead->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Note Content</label><textarea name="note" class="form-control" rows="3" required></textarea></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Note</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="followupModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.leads.add-followup', $lead->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Followup</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Followup Date</label><input type="date" name="followup_date" class="form-control" required></div>
        <div class="mb-2"><label>Notes</label><textarea name="notes" class="form-control" rows="3" required></textarea></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Followup</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="expenseModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.leads.add-expense', $lead->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Expense Type</label><input type="text" name="expense_type" class="form-control" required></div>
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="mb-2"><label>Receipt</label><input type="file" name="receipt" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Expense</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="dutySheetModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.leads.upload-duty-sheet', $lead->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Upload Duty Sheet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Duty Sheet File</label><input type="file" name="file" class="form-control" required></div>
        <div class="mb-2"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
    </form>
  </div>
</div>

@endsection
