@extends('admin.layout')
@section('title', 'Document Templates')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Document Templates</h2>
        <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Template
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" @selected(request('is_active') == '1')>Active</option>
                        <option value="0" @selected(request('is_active') == '0')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        value="{{ request('search') }}" placeholder="Template name...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Blade View</th>
                        <th>Default</th>
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
                                    <img src="{{ $template->thumbnail_url }}" width="60" height="40"
                                        style="object-fit:cover;border-radius:4px;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                        style="width:60px;height:40px;border-radius:4px;border:1px dashed #ccc;">
                                        <i class="fas fa-image text-muted small"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $template->name }}</strong>
                                @if ($template->description)
                                    <br><small class="text-muted">{{ Str::limit($template->description, 45) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background:{{ $template->category->color ?? '#6c757d' }}">
                                    <i class="{{ $template->category->icon ?? 'fas fa-file' }} me-1"></i>
                                    {{ $template->category->name ?? '—' }}
                                </span>
                            </td>
                            <td><code class="small">{{ $template->blade_view }}</code></td>
                            <td>
                                @if ($template->is_default)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Default</span>
                                @endif
                            </td>
                            <td>{{ $template->usage_count }}x</td>
                            <td>
                                <form method="POST"
                                    action="{{ route('admin.document-templates.toggle-status', $template->id) }}"
                                    style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="badge border-0 {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}"
                                        title="Click to toggle">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.document-templates.preview', $template->id) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary me-1" title="Preview PDF">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.document-templates.edit', $template->id) }}"
                                    class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST"
                                    action="{{ route('admin.document-templates.destroy', $template->id) }}"
                                    style="display:inline;" onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No templates found.
                                <a href="{{ route('admin.document-templates.create') }}">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $templates->links('pagination::bootstrap-4') }}</div>
@endsection
