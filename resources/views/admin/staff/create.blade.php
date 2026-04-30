@extends('admin.layout')
@section('title', 'Add New Staff')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>Add New Staff</h2>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @include('admin.partials.alerts')

    <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Basic Information</div>
            <div class="card-body row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Work & Salary Details -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Work & Salary Details</div>
            <div class="card-body row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Role / Staff Type *</label>
                    <select name="staff_type" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('staff_type') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Work Shift</label>
                    <select name="work_shift" class="form-select">
                        <option value="">No Shift / Flexible</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('work_shift') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date of Joining</label>
                    <input type="date" name="date_of_joining" class="form-control" value="{{ old('date_of_joining') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Salary Type</label>
                    <select name="salary_type" class="form-select">
                        <option value="">Select Type</option>
                        <option value="monthly" {{ old('salary_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="daily" {{ old('salary_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Basic Salary (₹)</label>
                    <input type="number" step="0.01" name="basic_salary" class="form-control"
                        value="{{ old('basic_salary') }}">
                </div>
            </div>
        </div>

        <!-- Section 3: Documents -->
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Documents Upload (Optional)</div>
            <div class="card-body row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">Staff Photo</label>
                    <input type="file" name="photo_file" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bank Passbook</label>
                    <input type="file" name="passbook_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-md-4 mb-3"></div> <!-- Empty spacer -->

                <!-- Aadhar -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Aadhar Number</label>
                    <input type="text" name="aadhar_number" class="form-control" value="{{ old('aadhar_number') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Aadhar Document</label>
                    <input type="file" name="aadhar_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- PAN -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">PAN Document</label>
                    <input type="file" name="pan_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- Driving License -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">DL Number</label>
                    <input type="text" name="dl_number" class="form-control" value="{{ old('dl_number') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DL Expiry Date</label>
                    <input type="date" name="dl_expiry" class="form-control" value="{{ old('dl_expiry') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DL Document</label>
                    <input type="file" name="dl_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- Badge -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Badge Number</label>
                    <input type="text" name="badge_number" class="form-control" value="{{ old('badge_number') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Badge Expiry Date</label>
                    <input type="date" name="badge_expiry" class="form-control" value="{{ old('badge_expiry') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Badge Document</label>
                    <input type="file" name="badge_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i> Save Staff
                Profile</button>
        </div>
    </form>
@endsection
