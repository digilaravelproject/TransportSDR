@extends('admin.layout')

@section('title', 'Manage Users')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Manage Users</h1>
            <p class="text-muted mb-3">List and manage platform users.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-users text-primary"></i>
                 </div>
                 <div class="label">Total Users</div>
                 <div class="value text-white">{{ $users->count() }}</div>
             </div>
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-check-circle text-success"></i>
                 </div>
                 <div class="label">Active Users</div>
                 <div class="value text-white">{{ $users->where('is_active', 1)->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: transparent; border: none; padding-bottom: 15px;">
                    <h5 class="mb-0 text-white fw-bold">All Users</h5>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add User
                    </a>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Tenant</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);">{{ ucfirst($user->role ?? 'N/A') }}</span>
                                    </td>
                                    <td>{{ $user->tenant->company_name ?? 'Super Admin' }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                        @else
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" onclick="return confirm('Are you sure?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center" style="color: #64748b;">No users found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection