@extends('admin.layout')

@section('title', 'Manage Routes')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Manage Routes</h2>
        <p class="text-muted">Create and assign vehicles to routes</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Route
        </a>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.routes.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name, origin or destination" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- List of Routes -->
<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Vehicle Count</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $route)
                <tr>
                    <td>{{ $route->id }}</td>
                    <td>{{ $route->name }}</td>
                    <td>{{ $route->origin }}</td>
                    <td>{{ $route->destination }}</td>
                    <td><span class="badge bg-info">{{ $route->vehicles_count }}</span></td>
                    <td>
                        @if ($route->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.routes.show', $route->id) }}" class="btn btn-sm btn-info text-white" title="View/Assign Vehicles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this route?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No routes found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-3">
            {{ $routes->links() }}
        </div>
    </div>
</div>
@endsection
