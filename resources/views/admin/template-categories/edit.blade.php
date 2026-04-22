@extends('admin.layout')
@section('title', 'Edit Category')

@section('content')
    <div class="mb-4">
        <h2><i class="fas fa-edit me-2"></i>Edit Category: {{ $templateCategory->name }}</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.template-categories.update', $templateCategory->id) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $templateCategory->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $templateCategory->slug) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $templateCategory->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Icon Class</label>
                                    <input type="text" name="icon" class="form-control"
                                        value="{{ old('icon', $templateCategory->icon) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" name="color" class="form-control form-control-color"
                                            value="{{ old('color', $templateCategory->color) }}" style="width:60px;">
                                        <input type="text" class="form-control"
                                            value="{{ old('color', $templateCategory->color) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', $templateCategory->sort_order) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', $templateCategory->is_active) == '1')>Active</option>
                                        <option value="0" @selected(old('is_active', $templateCategory->is_active) == '0')>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.template-categories.index') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Preview</h6>
                </div>
                <div class="card-body text-center">
                    <i class="{{ $templateCategory->icon ?? 'fas fa-file-alt' }} fa-3x mb-2"
                        style="color: {{ $templateCategory->color }};"></i>
                    <h5>{{ $templateCategory->name }}</h5>
                    <p class="text-muted small">{{ $templateCategory->description }}</p>
                    <span class="badge bg-info">{{ $templateCategory->templates()->count() }} templates</span>
                </div>
            </div>
        </div>
    </div>
@endsection
