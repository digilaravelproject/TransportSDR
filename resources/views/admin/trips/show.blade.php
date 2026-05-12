@extends('admin.layout')

@section('title', 'Trip Details')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Trip: <span style="color: #38bdf8;">{{ $trip->trip_number }}</span></h1>
            <p class="text-muted mb-2">View trip information, update status, and manage payments.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span>
                    @if($trip->status == 'completed')
                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Completed</span>
                    @elseif($trip->status == 'ongoing')
                        <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2);">Ongoing</span>
                    @elseif($trip->status == 'scheduled')
                        <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Scheduled</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">{{ ucfirst($trip->status) }}</span>
                    @endif
                </span>
                <span><i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($trip->trip_date)->format('d M Y, h:i A') }}</span>
                <span><i class="fas fa-user me-1"></i> {{ $trip->customer_name }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <a href="{{ route('admin.trips.index') }}" class="btn btn-sm mb-3" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Trips
            </a>
            <div class="text-end text-muted" style="font-size: 0.85rem;">
                <div>Total Amount: <strong class="text-success fs-6">₹{{ number_format($trip->total_amount, 2) }}</strong></div>
                <div>Advance: <strong class="text-warning">₹{{ number_format($trip->advance_amount, 2) }}</strong></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <!-- Left Column -->
        <div class="col-md-12 extra">
            <!-- Trip Information -->
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Trip Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Route</p>
                            <p class="text-white mb-0">{{ $trip->pickup_address ?? '-' }} <i class="fas fa-arrow-right mx-2 text-muted" style="font-size:10px;"></i> @if(!empty($trip->destination_points)) {{ collect($trip->destination_points)->pluck('name')->implode(', ') }} @else - @endif</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Customer Contact</p>
                            <p class="text-white mb-0">{{ $trip->customer_phone }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Vehicle Assigned</p>
                            <p class="text-white mb-0"><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ $trip->vehicle->registration_number ?? 'Not Assigned' }}</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Driver Assigned</p>
                            <p class="text-white mb-0">{{ $trip->driver->name ?? 'Not Assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h5 class="fw-bold text-white mb-0">Payments History</h5>
                    <button class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-plus me-1"></i> Add Payment</button>
                </div>
                <div class="table-responsive">
                    <table class="table shipment-table datatable mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trip->payments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td><strong class="text-success">₹{{ number_format($payment->amount, 2) }}</strong></td>
                                <td><span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">{{ ucfirst($payment->payment_mode) }}</span></td>
                                <td><span class="text-muted">{{ $payment->transaction_id ?? '-' }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No payments recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Update Status -->
            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h6 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Update Status</h6>
                <form action="{{ route('admin.trips.update-status', $trip->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <select name="status" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="scheduled" {{ $trip->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="ongoing" {{ $trip->status == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ $trip->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $trip->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">Update</button>
                    </div>
                </form>
            </div>

            <!-- KM Reading -->
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h6 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">KM Reading</h6>
                <form action="{{ route('admin.trips.update-km', $trip->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Start KM</label>
                        <input type="number" name="start_km" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ $trip->start_km }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">End KM</label>
                        <input type="number" name="end_km" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ $trip->end_km }}">
                    </div>
                    <button type="submit" class="btn btn-sm w-100" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">Update Reading</button>
                </form>
                @if($trip->total_km)
                    <div class="mt-3 text-center">
                        <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); padding: 8px 12px; font-size: 0.9rem;">
                            Total Distance: {{ $trip->total_km }} KM
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('admin.trips.add-payment', $trip->id) }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title text-dark">Add Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-dark">
        <div class="mb-2"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-2"><label>Payment Mode</label>
            <select name="payment_mode" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="online">Online/UPI</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <div class="mb-2"><label>Transaction ID (Optional)</label><input type="text" name="transaction_id" class="form-control"></div>
        <div class="mb-2"><label>Date</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
        <div class="mb-2"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-secondary">Save Payment</button></div>
    </form>
  </div>
</div>
@endsection
