
@extends('admin.layout')

@section('title', 'Vehicle Types')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Vehicle Types</h2>
        <p class="text-muted">Manage vehicle types used by the platform</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vehicle-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Add Vehicle Type
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Capacity</th>
                        <th>Price / KM</th>
                        <th>AC Extra</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleTypes as $type)
                    <tr>
                        <td>{{ $type->id }}</td>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->capacity }}</td>
                        <td>{{ number_format($type->price_per_km, 2) }}</td>
                        <td>{{ number_format($type->ac_extra_price, 2) }}</td>
                        <td>
                            @if($type->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.vehicle-types.show', $type->id) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.vehicle-types.edit', $type->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.vehicle-types.destroy', $type->id) }}" style="display:inline;">
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
                        <td colspan="7" class="text-center text-muted">No vehicle types found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
