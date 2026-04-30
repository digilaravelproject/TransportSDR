@extends('admin.layout')
@section('title', 'Staff Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-users me-2"></i>Staff Directory</h2>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Staff
        </a>
    </div>

    @include('admin.partials.alerts')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name / Details</th>
                        <th>Role & Shift</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        <tr>
                            <td>
                                <strong>{{ $member->name }}</strong><br>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $member->phone }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $member->role->name ?? 'N/A' }}</span><br>
                                <small class="text-muted">{{ $member->shift->name ?? 'No Shift' }}</small>
                            </td>
                            <td>₹{{ number_format($member->basic_salary, 2) }}</td>
                            <td>
                                <form action="{{ route('admin.staff.toggle-status', $member->id) }}" method="POST">
                                    @csrf
                                    <button class="badge border-0 {{ $member->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $member->is_active ? 'Active' : 'Suspended' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.staff.show', $member->id) }}" class="btn btn-sm btn-outline-info"
                                    title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.staff.edit', $member->id) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $staff->links() }}</div>
@endsection
