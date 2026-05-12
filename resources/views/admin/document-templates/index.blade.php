@extends('admin.layout')
@section('title', 'Document Templates')

@section('content')
<div class="dashboard-shell">
    <div class="top-row mb-4">
        <div class="welcome-col">
            <h1 class="fw-bold text-white mb-2">Document Templates</h1>
            <p class="text-muted mb-3">Manage document templates and previews.</p>
        </div>
        <div class="cards-col">
             <div class="stat-card">
                 <div class="stat-pill">
                     <i class="fas fa-file-alt text-primary"></i>
                 </div>
                 <div class="label">Total Templates</div>
                 <div class="value text-white">{{ collect($templates->items())->count() }}</div>
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

            <div class="dashboard-card mb-4" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="category_id" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="is_active" class="form-select" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            <option value="">All Statuses</option>
                            <option value="1" @selected(request('is_active') == '1')>Active</option>
                            <option value="0" @selected(request('is_active') == '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="{{ request('search') }}" placeholder="Template name...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm w-100 h-100" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="dashboard-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 24px rgba(2,6,23,0.6)">
                <div class="card-header d-flex justify-content-between align-items-center mb-3" style="background: transparent; border: none; padding-bottom: 0;">
                    <h5 class="mb-0 text-white fw-bold">Templates Directory</h5>
                    <a href="{{ route('admin.document-templates.create') }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;">
                        <i class="fas fa-plus me-1"></i> Add Template
                    </a>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table shipment-table datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Blade View</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td>
                                            @if ($template->thumbnail)
                                                <img src="{{ $template->thumbnail_url }}" width="60" height="40" style="object-fit:cover; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center" style="width:60px; height:40px; border-radius:6px; border:1px dashed rgba(255,255,255,0.2); background: rgba(0,0,0,0.2);">
                                                    <i class="fas fa-image text-muted small"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-white">{{ $template->name }}</strong>
                                            @if ($template->is_default)
                                                <span class="badge ms-2" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2);"><i class="fas fa-star me-1" style="font-size: 10px;"></i> Default</span>
                                            @endif
                                            @if ($template->description)
                                                <br><small class="text-muted" style="font-size: 0.8rem;">{{ Str::limit($template->description, 45) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge" style="background: rgba(255,255,255,0.05); color: {{ $template->category->color ?? '#cbd5e1' }}; border: 1px solid rgba(255,255,255,0.1);">
                                                <i class="{{ $template->category->icon ?? 'fas fa-file' }} me-1"></i>
                                                {{ $template->category->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td><code style="color: #38bdf8; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 4px;">{{ $template->blade_view }}</code></td>
                                        <td><strong class="text-white">{{ $template->usage_count }}x</strong></td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.document-templates.toggle-status', $template->id) }}" style="display:inline; margin: 0;">
                                                @csrf @method('PATCH')
                                                @if($template->is_active)
                                                    <button type="submit" class="badge" style="background: #113627; color: #34d399; border: 1px solid #1a513b; cursor: pointer;" title="Click to toggle">Active</button>
                                                @else
                                                    <button type="submit" class="badge" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); cursor: pointer;" title="Click to toggle">Inactive</button>
                                                @endif
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.document-templates.preview', $template->id) }}" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;" title="Preview PDF">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.document-templates.edit', $template->id) }}" class="btn btn-sm" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 6px;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.document-templates.destroy', $template->id) }}" style="display:inline;" onsubmit="return confirm('Delete this template?')">
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
                                            No templates found.
                                            <a href="{{ route('admin.document-templates.create') }}" style="color: #38bdf8; text-decoration: underline;">Create one</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">{{ $templates->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
