@extends('admin.layout')
@section('title', 'Template Categories')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Template Categories</h1>
            <p class="text-muted mb-3">Manage document template categories.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-tags text-primary"></i>
                 </div>
                 <div class="label">Total Categories</div>
                 <div class="value text-white">{{ collect($categories->items())->count() }}</div>
             </div>
        </div>
    </div>
    
    <div class="dashboard-main">
        <div class="left-panel" style="flex: 1;">
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.2);">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
                </div>
            @endif

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Categories Directory</h5>
                    <a href="{{ route('admin.template-categories.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Category
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Slug</th>
                                    <th>Templates</th>
                                    <th>Status</th>
                                    <th>Sort</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $cat)
                                    <tr>
                                        <td>{{ $cat->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.05); color: {{ $cat->color }};">
                                                    <i class="{{ $cat->icon ?? 'fas fa-file' }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <strong class="text-white">{{ $cat->name }}</strong>
                                                    @if ($cat->description)
                                                        <br><small class="text-muted" style="font-size: 0.8rem;">{{ Str::limit($cat->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td><code style="color: #38bdf8; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 4px;">{{ $cat->slug }}</code></td>
                                        <td><span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2);">{{ $cat->templates_count }} templates</span></td>
                                        <td>
                                            @if ($cat->is_active)
                                                <span class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b;">Active</span>
                                            @else
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2);">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $cat->sort_order }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.template-categories.show', $cat->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.template-categories.edit', $cat->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.template-categories.destroy', $cat->id) }}" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 6px;" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center" style="color: #64748b; padding: 2rem;">
                                            No categories found.
                                            <a href="{{ route('admin.template-categories.create') }}" style="color: #38bdf8; text-decoration: underline;">Create one</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">{{ $categories->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
