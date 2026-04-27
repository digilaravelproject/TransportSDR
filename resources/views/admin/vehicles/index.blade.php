@extends('admin.layout')

@section('title', 'Manage Vehicles')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Manage Vehicles</h2>
        <p class="text-muted">List and manage fleet vehicles</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Add Vehicle
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Registration</th>
                        <th>Type</th>
                        <th>Year</th>
                        <th>Tenant</th>
                        <th>Available</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                    <tr>
                        <td>{{ $v->id }}</td>
                        <td>{{ $v->registration_number }}</td>
                        <td>{{ $v->type }}</td>
                        <td>{{ $v->model_year }}</td>
                        <td>{{ $v->tenant->company_name ?? '—' }}</td>
                        <td>{!! $v->is_available ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                        <td>{!! $v->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td>
                        <td>
                            <a href="{{ route('admin.vehicles.edit', $v->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('admin.vehicles.show', $v->id) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-eye"></i></a>
                            <form method="POST" action="{{ route('admin.vehicles.destroy', $v->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No vehicles found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
