@extends('admin.layout')

@section('title', $shift->name)

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col" style="flex: 2;">
            <h1 class="fw-bold text-white mb-2">Shift: <span style="color: #38bdf8;">{{ $shift->name }}</span></h1>
            <p class="text-muted mb-2">Shift overview and assigned drivers.</p>
            <div class="d-flex gap-3 text-muted" style="font-size: 0.9rem;">
                <span>
                    @if($shift->is_active)
                        <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                    @endif
                </span>
                <span>
                    @if($shift->type === 'regular')
                        <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">Regular</span>
                    @elseif($shift->type === 'overtime')
                        <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">Overtime</span>
                    @elseif($shift->type === 'night')
                        <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Night</span>
                    @else
                        <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2);">Custom</span>
                    @endif
                </span>
                <span><i class="fas fa-calendar-alt me-1"></i> {{ optional($shift->date)->format('d M Y') ?: 'N/A' }}</span>
            </div>
        </div>
        <div class="cards-col" style="flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-sm" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 6px;">
                <i class="fas fa-edit me-1"></i> Edit Shift
            </a>
            <form method="POST" action="{{ route('admin.shifts.destroy', $shift->id) }}" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure you want to delete this shift?');">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
    
    <div class="dashboard-main row g-4">
        <!-- Left Column: Shift Details -->
        <div class="col-md-7">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <h5 class="fw-bold text-white mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Shift Details</h5>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Time Range</p>
                            <p class="text-white mb-0 fs-5"><i class="far fa-clock me-2" style="color: #38bdf8;"></i>{{ $shift->time_range }}</p>
                        </div>
                    </div>

                    @if($shift->notes)
                        <div class="col-12 mt-4">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Notes</p>
                            <p class="text-white mb-0" style="font-size: 0.95rem; line-height: 1.5; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">{{ $shift->notes }}</p>
                        </div>
                    @endif

                    <div class="col-12 mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                            <span>Created: {{ $shift->created_at->format('d M Y, H:i') }}</span>
                            <span>Updated: {{ $shift->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Assigned Drivers -->
        <div class="col-md-5">
            <div class="dashboard-card h-100" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="d-flex justify-content-between align-items-center mb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                    <h5 class="mb-0 text-white fw-bold">Assigned Drivers</h5>
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">{{ $shift->drivers->count() }} Total</span>
                </div>
                
                <form method="POST" action="{{ route('admin.shifts.add-driver', $shift->id) }}" class="mb-4 p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    @csrf
                    <label class="form-label text-muted small mb-2">Assign Driver</label>
                    <div class="input-group">
                        <select name="driver_id" class="form-select" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #fff;" required>
                            <option value="">-- Select Driver --</option>
                            @foreach($availableDrivers as $dr)
                                @if(!$shift->drivers->contains('id', $dr->id))
                                    <option value="{{ $dr->id }}">{{ $dr->name }} ({{ $dr->phone }})</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="btn" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);">
                            Assign
                        </button>
                    </div>
                </form>

                <ul class="list-group list-group-flush">
                    @forelse($shift->drivers as $driver)
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 mb-2 border-bottom border-secondary pb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); color: #fbbf24;">
                                    <i class="far fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white">{{ $driver->name }}</h6>
                                    <small class="text-muted">Driver</small>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.shifts.remove-driver', $shift->id) }}" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="driver_id" value="{{ $driver->id }}">
                                <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" title="Remove" onclick="return confirm('Remove driver?');">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent border-0 text-muted text-center py-4">No drivers assigned yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
