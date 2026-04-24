@extends('admin.layout')
@section('title', $templateCategory->name)

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold mb-0"><i class="{{ $templateCategory->icon ?? 'fas fa-tags' }} me-2" style="color: {{ $templateCategory->color }};"></i> {{ $templateCategory->name }}</h2>
            <p class="text-muted">Category details and templates</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.document-templates.create') }}?category={{ $templateCategory->id }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Add Template
                </a>
                <a href="{{ route('admin.template-categories.edit', $templateCategory->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <a href="{{ route('admin.template-categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $templateCategory->templates_count }}</h3>
                    <small class="text-muted">Total Templates</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $templateCategory->templates()->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <code class="h5">{{ $templateCategory->slug }}</code>
                    <br><small class="text-muted">Slug</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    @if ($templateCategory->is_active)
                        <span class="badge bg-success fs-6">Active</span>
                    @else
                        <span class="badge bg-secondary fs-6">Inactive</span>
                    @endif
                    <br><small class="text-muted">Status</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Templates in this Category</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Name</th>
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
                                        style="width:60px;height:40px;border-radius:4px;">
                                        <i class="fas fa-file-pdf text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $template->name }}</strong>
                                @if ($template->description)
                                    <br><small class="text-muted">{{ Str::limit($template->description, 40) }}</small>
                                @endif
                            </td>
                            <td><code class="small">{{ $template->blade_view }}</code></td>
                            <td>
                                @if ($template->is_default)
                                    <span class="badge bg-warning text-dark">Default</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $template->usage_count }}x</span></td>
                            <td>
                                @if ($template->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.document-templates.preview', $template->id) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary me-1" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.document-templates.edit', $template->id) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                No templates yet.
                                <a
                                    href="{{ route('admin.document-templates.create') }}?category={{ $templateCategory->id }}">Add
                                    one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
