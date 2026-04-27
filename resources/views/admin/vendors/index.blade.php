@extends('admin.layout')

@section('title', 'Manage Vendors')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="fw-bold mb-0">Manage Vendors</h2>
        <p class="text-muted">List and manage vendors</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Add Vendor
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
                        <th>Name</th>
                        <th>Contract</th>
                        <th>Tenant</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Monthly</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $v)
                    <tr>
                        <td>{{ $v->id }}</td>
                        <td>{{ $v->vendor_name }}</td>
                        <td>{{ $v->contract_name }}</td>
                        <td>{{ $v->tenant->company_name ?? '—' }}</td>
                        <td>{{ optional($v->start_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($v->end_date)->format('Y-m-d') }}</td>
                        <td>{{ $v->monthly_amount }}</td>
                        <td>
                            <a href="{{ route('admin.vendors.edit', $v->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('admin.vendors.show', $v->id) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-eye"></i></a>
                            <form method="POST" action="{{ route('admin.vendors.destroy', $v->id) }}" style="display:inline;">
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
                        <td colspan="8" class="text-center text-muted">No vendors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
