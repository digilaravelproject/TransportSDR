@extends('admin.layout')
@section('title', 'Edit Template')

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold mb-0"><i class="fas fa-edit me-2"></i>Edit: {{ $documentTemplate->name }}</h2>
            <p class="text-muted">Modify template contents and settings</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.document-templates.update', $documentTemplate->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-select" required>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', $documentTemplate->category_id) == $cat->id)>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $documentTemplate->slug) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $documentTemplate->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blade View Path *</label>
                            <input type="text" name="blade_view" class="form-control"
                                value="{{ old('blade_view', $documentTemplate->blade_view) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Variables (JSON)</label>
                            <textarea name="variables" class="form-control" rows="4">{{ old('variables', $documentTemplate->variables ? json_encode($documentTemplate->variables, JSON_PRETTY_PRINT) : '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sample Data (JSON)</label>
                            <textarea name="sample_data" class="form-control" rows="5">{{ old('sample_data', $documentTemplate->sample_data ? json_encode($documentTemplate->sample_data, JSON_PRETTY_PRINT) : '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thumbnail Image</label>
                            @if ($documentTemplate->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ $documentTemplate->thumbnail_url }}" height="80"
                                        style="border-radius:4px;border:1px solid #dee2e6;">
                                    <small class="text-muted ms-2">Current thumbnail</small>
                                </div>
                            @endif
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            <small class="text-muted">New image upload karooge to purana replace ho jaayega</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', $documentTemplate->sort_order) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', $documentTemplate->is_active) == '1')>Active</option>
                                        <option value="0" @selected(!old('is_active', $documentTemplate->is_active))>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Set as Default</label>
                                    <select name="is_default" class="form-select">
                                        <option value="0" @selected(!old('is_default', $documentTemplate->is_default))>No</option>
                                        <option value="1" @selected(old('is_default', $documentTemplate->is_default))>Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-secondary">Back</a>
                            <a href="{{ route('admin.document-templates.preview', $documentTemplate->id) }}"
                                target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-eye me-1"></i>Preview PDF
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6>Template Info</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Category:</strong>
                        <span class="badge" style="background:{{ $documentTemplate->category->color ?? '#6c757d' }}">
                            {{ $documentTemplate->category->name ?? '—' }}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Usage:</strong> {{ $documentTemplate->usage_count }} times</p>
                    <p class="mb-1"><strong>Created:</strong>
                        {{ $documentTemplate->created_at->format('d M Y') }}
                    </p>
                    <p class="mb-0"><strong>Updated:</strong>
                        {{ $documentTemplate->updated_at->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
