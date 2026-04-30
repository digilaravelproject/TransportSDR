@extends('admin.layout')
@section('title', 'Staff Profile - ' . $staff->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">{{ $staff->name }}'s Profile</h2>
            <span class="badge bg-primary">{{ $staff->role->name ?? 'Staff' }}</span>
            <span class="text-muted ms-2"><i class="fas fa-phone"></i> {{ $staff->phone }}</span>
        </div>
        <div>
            <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button">Overview &
                Docs</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#duty" type="button">Duty Logs</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#finance" type="button">Salary &
                Advances</button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content">

        <!-- OVERVIEW TAB -->
        <div class="tab-pane fade show active" id="overview">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header fw-bold">Basic Information</div>
                        <div class="card-body">
                            <table class="table table-sm borderless">
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $staff->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td>{{ $staff->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Shift:</th>
                                    <td>{{ $staff->shift->name ?? 'N/A' }} ({{ $staff->shift->formatted_start_time ?? '' }})
                                    </td>
                                </tr>
                                <tr>
                                    <th>Join Date:</th>
                                    <td>{{ $staff->date_of_joining ? $staff->date_of_joining->format('d M Y') : 'N/A' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header fw-bold">Uploaded Documents</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($staff->documents as $doc)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</strong><br>
                                            <small class="text-muted">No: {{ $doc->document_number ?? 'N/A' }}</small>
                                        </div>
                                        <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-info">View</a>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No documents uploaded.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DUTY LOGS TAB -->
        <div class="tab-pane fade" id="duty">
            <div class="card">
                <div class="card-header fw-bold">Recent Attendance & Duty</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
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
                                    <td><span class="badge bg-secondary">{{ ucfirst($log->status) }}</span></td>
                                    <td>{{ $log->trip_purpose ?? '—' }}</td>
                                    <td>{{ $log->working_hours ? $log->working_hours . ' hrs' : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No duty logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FINANCE TAB (Salary & Advance) -->
        <div class="tab-pane fade" id="finance">
            <div class="row">
                <div class="col-md-7">
                    <div class="card mb-3">
                        <div class="card-header fw-bold">Salary History</div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Month/Year</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salaries as $salary)
                                        <tr>
                                            <td>{{ $salary->month }} / {{ $salary->year }}</td>
                                            <td>₹{{ number_format($salary->net_salary, 2) }}</td>
                                            <td><span
                                                    class="badge {{ $salary->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($salary->payment_status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning text-dark fw-bold">Advances Given</div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($advances as $adv)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($adv->advance_date)->format('d M y') }}</td>
                                            <td>₹{{ number_format($adv->amount, 2) }}</td>
                                            <td>{{ $adv->is_deducted ? 'Deducted' : 'Pending' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
