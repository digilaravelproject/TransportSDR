@extends('admin.layout')
@section('title', 'Staff Profile - ' . $staff->name)

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">{{ $staff->name }}'s Profile</h1>
            <p class="text-muted mb-2">View staff details, duty logs, and history.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $staff->role->name ?? 'Staff' }}</span></span>
                <span><i class="fas fa-phone me-1"></i> {{ $staff->phone }}</span>
                <span><i class="fas fa-envelope me-1"></i> {{ $staff->email ?? 'N/A' }}</span>
                <span><i class="fas fa-calendar-check me-1"></i> Joined: {{ $staff->date_of_joining ? $staff->date_of_joining->format('d M Y') : 'N/A' }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <div class="d-flex gap-2 mb-3">
                <a href="{{ route('admin.staff.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                
                <ul class="nav nav-pills mb-4" id="staffTabs" role="tablist" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview" type="button" style="color: #cbd5e1; background: transparent;">Overview & Docs</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#duty" type="button" style="color: #cbd5e1; background: transparent;">Duty Logs</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#finance" type="button" style="color: #cbd5e1; background: transparent;">Salary & Advances</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#trips" type="button" style="color: #cbd5e1; background: transparent;">Trips</button>
                    </li>
                </ul>

                <style>
                    .nav-pills .nav-link.active { background: rgba(14, 165, 233, 0.1) !important; color: #38bdf8 !important; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 8px; }
                </style>

                <div class="tab-content" id="staffTabsContent">
                    
                    <!-- OVERVIEW TAB -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card h-100 border-0" style="background: rgba(0,0,0,0.2); border-radius: 12px;">
                                    <div class="card-header border-0 text-white fw-bold pb-0 pt-3 bg-transparent">Basic Information</div>
                                    <div class="card-body">
                                        <table class="table table-sm borderless text-muted mb-0">
                                            <tr>
                                                <th class="border-0" style="width: 120px;">Address:</th>
                                                <td class="border-0 text-white">{{ $staff->address ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">Shift:</th>
                                                <td class="border-0 text-white">{{ $staff->shift->name ?? 'N/A' }} ({{ $staff->shift->formatted_start_time ?? '' }})</td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">Basic Salary:</th>
                                                <td class="border-0 text-white">₹{{ number_format($staff->basic_salary, 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 border-0" style="background: rgba(0,0,0,0.2); border-radius: 12px;">
                                    <div class="card-header border-0 text-white fw-bold pb-0 pt-3 bg-transparent d-flex justify-content-between align-items-center">
                                        <span>Uploaded Documents</span>
                                        <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#docsModal">Upload Doc</button>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @forelse($staff->documents as $doc)
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 mb-2 border-bottom border-secondary pb-2">
                                                    <div>
                                                        <strong class="text-white">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</strong><br>
                                                        <small class="text-muted">No: {{ $doc->document_number ?? 'N/A' }}</small>
                                                    </div>
                                                    <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px;">View</a>
                                                </li>
                                            @empty
                                                <li class="list-group-item bg-transparent border-0 text-muted px-0">No documents uploaded.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DUTY LOGS TAB -->
                    <div class="tab-pane fade" id="duty" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Recent Attendance & Duty</h6>
                            <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#dutyModal">Add Duty</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Trip/Purpose</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dutyLogs as $log)
                                        <tr>
                                            <td>{{ $log->date->format('d M Y') }}</td>
                                            <td>
                                                @if($log->status == 'present')
                                                    <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Present</span>
                                                @elseif($log->status == 'absent')
                                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Absent</span>
                                                @else
                                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Leave</span>
                                                @endif
                                            </td>
                                            <td>{{ $log->trip_purpose ?? '—' }}</td>
                                            <td>{{ $log->working_hours ? $log->working_hours . ' hrs' : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No duty logs found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- FINANCE TAB -->
                    <div class="tab-pane fade" id="finance" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="card h-100 border-0" style="background: rgba(0,0,0,0.2); border-radius: 12px;">
                                    <div class="card-header border-0 text-white fw-bold pb-0 pt-3 bg-transparent">Salary History</div>
                                    <div class="card-body p-0">
                                        <table class="table shipment-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Month/Year</th>
                                                    <th>Net Salary</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($salaries as $salary)
                                                    <tr>
                                                        <td>{{ $salary->month }} / {{ $salary->year }}</td>
                                                        <td>₹{{ number_format($salary->net_salary, 2) }}</td>
                                                        <td>
                                                            @if($salary->payment_status == 'paid')
                                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Paid</span>
                                                            @else
                                                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Pending</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center text-muted py-3">No salary records found.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card h-100 border-0" style="background: rgba(0,0,0,0.2); border-radius: 12px; border-left: 3px solid #f59e0b !important;">
                                    <div class="card-header border-0 text-white fw-bold pb-0 pt-3 bg-transparent d-flex justify-content-between align-items-center">
                                        <span>Advances Given</span>
                                        <button class="btn btn-sm" style="background: rgba(245, 158, 11, 0.1); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#advanceModal">Give Advance</button>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table shipment-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($advances as $adv)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($adv->advance_date)->format('d M y') }}</td>
                                                        <td>₹{{ number_format($adv->amount, 2) }}</td>
                                                        <td>
                                                            @if($adv->is_deducted)
                                                                <span class="text-success"><i class="fas fa-check-circle"></i> Deducted</span>
                                                            @else
                                                                <span class="text-warning"><i class="fas fa-clock"></i> Pending</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center text-muted py-3">No advances given.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TRIPS TAB -->
                    <div class="tab-pane fade" id="trips" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-white m-0">Recent Trips</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table shipment-table datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Trip Number</th>
                                        <th>Status</th>
                                        <th>Origin -> Dest</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trips as $trip)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('d M Y') }}</td>
                                            <td><strong class="text-white">{{ $trip->trip_number ?? '#' . $trip->id }}</strong></td>
                                            <td><span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">{{ ucfirst($trip->status) }}</span></td>
                                            <td><span class="text-muted">{{ $trip->pickup_address ?? '-' }}</span> <i class="fas fa-arrow-right mx-1" style="font-size:10px;"></i> <span class="text-white">@if(!empty($trip->destination_points)) {{ collect($trip->destination_points)->pluck('name')->implode(', ') }} @else - @endif</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No trips found.</td></tr>
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
<div class="modal fade" id="docsModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.staff.document', $staff->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Upload Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Type</label>
            <select name="document_type" class="form-select" required>
                <option value="aadhar">Aadhar</option>
                <option value="pan">PAN</option>
                <option value="dl">Driving License</option>
                <option value="badge">Badge</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="mb-2"><label>Number</label><input type="text" name="document_number" class="form-control"></div>
        <div class="mb-2"><label>File</label><input type="file" name="file" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="dutyModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.staff.attendance', $staff->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Duty Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Date</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
        <div class="mb-2"><label>Status</label>
            <select name="status" class="form-select" required>
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="leave">Leave</option>
            </select>
        </div>
        <div class="mb-2"><label>Purpose/Notes</label><input type="text" name="notes" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="advanceModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.staff.advance', $staff->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Give Advance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Date</label><input type="date" name="advance_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
        <div class="mb-2"><label>Reason</label><input type="text" name="reason" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Give Advance</button></div>
    </form>
  </div>
</div>
@endsection
