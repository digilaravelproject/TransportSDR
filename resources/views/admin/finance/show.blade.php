@extends('admin.layout')

@section('title','Finance Details')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Finance Entry <span style="color: #38bdf8;">#{{ $entry->id }}</span></h1>
            <p class="text-muted mb-2">View detailed information about this financial transaction.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span>
                    @if(strtolower($entry->entry_type) == 'income')
                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;"><i class="fas fa-arrow-down me-1"></i> Income</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2)"><i class="fas fa-arrow-up me-1"></i> Expense</span>
                    @endif
                </span>
                <span><i class="fas fa-calendar-alt me-1"></i> {{ optional($entry->entry_date)->format('d M Y') }}</span>
                <span><i class="fas fa-tags me-1"></i> {{ ucfirst($entry->category) }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
            <a href="{{ route('admin.finance.index') }}" class="btn btn-sm mb-3" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Cashbook
            </a>
            <div class="text-end text-muted" style="font-size: 0.85rem;">
                <div>Amount: <strong class="{{ strtolower($entry->entry_type) == 'income' ? 'text-success' : 'text-danger' }} fs-5">₹{{ number_format($entry->amount, 2) }}</strong></div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <div class="col-md-12 extra mx-auto">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Transaction Details</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Payment Method</p>
                            <p class="text-white mb-0"><span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ ucfirst($entry->payment_mode) }}</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Reference Number</p>
                            <p class="text-white mb-0">{{ $entry->reference_number ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Description</p>
                            <p class="text-white mb-0">{{ $entry->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>

                @if($entry->receipt_path)
                    <div class="mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">Attachments</p>
                        <a href="{{ asset('storage/' . $entry->receipt_path) }}" target="_blank" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                            <i class="fas fa-file-download me-2"></i> Download Receipt
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
