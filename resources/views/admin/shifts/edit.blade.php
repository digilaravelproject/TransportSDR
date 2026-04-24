@extends('admin.layout')

@section('title', 'Edit Shift')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Edit Shift: {{ $shift->name }}</h2>
        <p class="text-muted">Change shift timings and details</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following issues:
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $shift->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', $shift->start_time) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', $shift->end_time) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Shift Type *</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="regular" @selected(old('type', $shift->type) === 'regular')>Regular</option>
                                    <option value="overtime" @selected(old('type', $shift->type) === 'overtime')>Overtime</option>
                                    <option value="night" @selected(old('type', $shift->type) === 'night')>Night</option>
                                    <option value="custom" @selected(old('type', $shift->type) === 'custom')>Custom</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_active" class="form-label">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $shift->is_active))>
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days of Week</label>
                        <div class="row">
                            @foreach(['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'] as $day => $name)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="day{{ $day }}" @checked(in_array($day, old('days', $shift->days ?? [])))>
                                        <label class="form-check-label" for="day{{ $day }}">
                                            {{ $name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_drivers" class="form-label">Max Drivers</label>
                                <input type="number" class="form-control @error('max_drivers') is-invalid @enderror" id="max_drivers" name="max_drivers" value="{{ old('max_drivers', $shift->max_drivers) }}" min="1">
                                @error('max_drivers')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hourly_rate" class="form-label">Hourly Rate (RS)</label>
                                <input type="number" class="form-control @error('hourly_rate') is-invalid @enderror" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $shift->hourly_rate) }}" step="0.01" min="0">
                                @error('hourly_rate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $shift->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
