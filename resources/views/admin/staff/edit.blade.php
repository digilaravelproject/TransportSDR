@extends('admin.layout')
@section('title', 'Edit Staff - ' . $staff->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-user-edit me-2"></i>Edit Staff: {{ $staff->name }}</h2>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @include('admin.partials.alerts')

    <form action="{{ route('admin.staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Basic Information</div>
            <div class="card-body row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $staff->name) }}"
                        required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}"
                        required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $staff->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Work & Settings -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Work & Settings</div>
            <div class="card-body row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Role / Staff Type *</label>
                    <select name="staff_type" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('staff_type', $staff->staff_type) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Work Shift</label>
                    <select name="work_shift" class="form-select">
                        <option value="">No Shift / Flexible</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}"
                                {{ old('work_shift', $staff->work_shift) == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date of Joining</label>
                    <input type="date" name="date_of_joining" class="form-control"
                        value="{{ old('date_of_joining', $staff->date_of_joining ? \Carbon\Carbon::parse($staff->date_of_joining)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Salary Type</label>
                    <select name="salary_type" class="form-select">
                        <option value="">Select Type</option>
                        <option value="monthly"
                            {{ old('salary_type', $staff->salary_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="daily" {{ old('salary_type', $staff->salary_type) == 'daily' ? 'selected' : '' }}>
                            Daily</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Basic Salary (₹)</label>
                    <input type="number" step="0.01" name="basic_salary" class="form-control"
                        value="{{ old('basic_salary', $staff->basic_salary) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Assigned Vehicle</label>
                    <input type="text" name="assigned_vehicle" class="form-control"
                        value="{{ old('assigned_vehicle', $staff->assigned_vehicle) }}">
                </div>

                <!-- Status Toggles -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Is Available (Not on trip)?</label>
                    <select name="is_available" class="form-select">
                        <option value="1" {{ old('is_available', $staff->is_available) == 1 ? 'selected' : '' }}>Yes,
                            Available</option>
                        <option value="0" {{ old('is_available', $staff->is_available) == 0 ? 'selected' : '' }}>No,
                            On Trip</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Account Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $staff->is_active) == 1 ? 'selected' : '' }}>Active
                        </option>
                        <option value="0" {{ old('is_active', $staff->is_active) == 0 ? 'selected' : '' }}>Suspended /
                            Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Update Documents -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Update Documents (Leave file inputs empty to keep current files)</div>
            <div class="card-body row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">Staff Photo</label>
                    <input type="file" name="photo_file" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bank Passbook</label>
                    <input type="file" name="passbook_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-md-4 mb-3"></div>

                <!-- Aadhar -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Aadhar Number</label>
                    <input type="text" name="aadhar_number" class="form-control"
                        value="{{ old('aadhar_number', $staff->aadhar_number ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Update Aadhar File</label>
                    <input type="file" name="aadhar_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- PAN -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control"
                        value="{{ old('pan_number', $staff->pan_number ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Update PAN File</label>
                    <input type="file" name="pan_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- Driving License -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">DL Number</label>
                    <input type="text" name="dl_number" class="form-control"
                        value="{{ old('dl_number', $staff->dl_number ?? '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DL Expiry Date</label>
                    <input type="date" name="dl_expiry" class="form-control"
                        value="{{ old('dl_expiry', $staff->dl_expiry ? \Carbon\Carbon::parse($staff->dl_expiry)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Update DL File</label>
                    <input type="file" name="dl_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- Badge -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Badge Number</label>
                    <input type="text" name="badge_number" class="form-control"
                        value="{{ old('badge_number', $staff->badge_number ?? '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Badge Expiry Date</label>
                    <input type="date" name="badge_expiry" class="form-control"
                        value="{{ old('badge_expiry', $staff->badge_expiry ? \Carbon\Carbon::parse($staff->badge_expiry)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Update Badge File</label>
                    <input type="file" name="badge_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i> Update Staff
                Profile</button>
        </div>
    </form>
@endsection
