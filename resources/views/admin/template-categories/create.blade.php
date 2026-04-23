@extends('admin.layout')
@section('title', 'Create Category')

@section('content')
    <div class="mb-4">
        <h2><i class="fas fa-plus me-2"></i>Create Template Category</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.template-categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="e.g. Invoice, Letterhead, Quotation" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug <small class="text-muted">(auto generated)</small></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug') }}" placeholder="invoice">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Short description">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Icon Class <small
                                            class="text-muted">(FontAwesome)</small></label>
                                    <input type="text" name="icon" class="form-control"
                                        value="{{ old('icon', 'fas fa-file-alt') }}" placeholder="fas fa-file-invoice">
                                    <small class="text-muted">
                                        Example: <code>fas fa-file-invoice</code>, <code>fas fa-envelope</code>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" name="color" class="form-control form-control-color"
                                            value="{{ old('color', '#6c757d') }}" style="width:60px;">
                                        <input type="text" id="colorText" class="form-control"
                                            value="{{ old('color', '#6c757d') }}" placeholder="#6c757d">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', '1') == '1')>Active</option>
                                        <option value="0" @selected(old('is_active') == '0')>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.template-categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Preview</h6>
                </div>
                <div class="card-body text-center">
                    <i id="previewIcon" class="fas fa-file-alt fa-3x mb-2" style="color:#6c757d;"></i>
                    <h5 id="previewName">Category Name</h5>
                    <p class="text-muted small" id="previewDesc">Description here</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview
        document.querySelector('[name=name]').addEventListener('input', function() {
            document.getElementById('previewName').textContent = this.value || 'Category Name';
            // Auto slug
            document.querySelector('[name=slug]').value = this.value.toLowerCase().replace(/\s+/g, '-').replace(
                /[^a-z0-9-]/g, '');
        });

        document.querySelector('[name=description]').addEventListener('input', function() {
            document.getElementById('previewDesc').textContent = this.value || 'Description here';
        });

        document.querySelector('[name=icon]').addEventListener('input', function() {
            document.getElementById('previewIcon').className = this.value + ' fa-3x mb-2';
        });

        const colorPicker = document.querySelector('[name=color]');
        const colorText = document.getElementById('colorText');

        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
            document.getElementById('previewIcon').style.color = this.value;
        });

        colorText.addEventListener('input', function() {
            colorPicker.value = this.value;
            document.getElementById('previewIcon').style.color = this.value;
        });
    </script>
@endsection
