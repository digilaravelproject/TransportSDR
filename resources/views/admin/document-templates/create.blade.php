@extends('admin.layout')
@section('title', 'Create Template')

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold mb-0"><i class="fas fa-plus me-2"></i>Create Document Template</h2>
            <p class="text-muted">Build a document template for invoices, letters and more</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.document-templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                
                                        placeholder="e.g. Invoice with GST" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', request('category')) == $cat->id)>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug <small class="text-muted">(auto generated)</small></label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}"
                                placeholder="invoice-with-gst">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this template">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blade View Path *
                                <small class="text-muted">(resources/views/ se relative)</small>
                            </label>
                            <input type="text" name="blade_view"
                                class="form-control @error('blade_view') is-invalid @enderror"
                                value="{{ old('blade_view') }}" placeholder="pdf.templates.invoice-gst" required>
                            <small class="text-muted">
                                Example: <code>pdf.templates.invoice-gst</code>,
                                <code>pdf.templates.letterhead-simple</code>
                            </small>
                            @error('blade_view')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Variables (JSON)
                                <small class="text-muted">— template mein kaunse variables use hote hain</small>
                            </label>
                            <textarea name="variables" class="form-control" rows="4"
                                placeholder='[{"key":"trip","label":"Trip Data","type":"object"},{"key":"tenant","label":"Company Info","type":"object"}]'>{{ old('variables') }}</textarea>
                            <small class="text-muted">JSON array format mein</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sample Data (JSON)
                                <small class="text-muted">— preview ke liye dummy data</small>
                            </label>
                            <textarea name="sample_data" class="form-control" rows="5"
                                placeholder='{"trip":{"trip_number":"TRP-2026-0001","trip_route":"Lucknow to Delhi"},"tenant":{"company_name":"Demo Company"}}'>{{ old('sample_data') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thumbnail Image</label>
                            <input type="file" name="thumbnail" class="form-control"
                                accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted">JPG/PNG, max 2MB — template ka preview screenshot</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', '1') == '1')>Active</option>
                                        <option value="0" @selected(old('is_active') == '0')>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Set as Default</label>
                                    <select name="is_default" class="form-select">
                                        <option value="0" @selected(old('is_default', '0') == '0')>No</option>
                                        <option value="1" @selected(old('is_default') == '1')>Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle me-1"></i>Available Categories</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($categories as $cat)
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <i class="{{ $cat->icon ?? 'fas fa-file' }}" style="color:{{ $cat->color }};"></i>
                                <div>
                                    <strong>{{ $cat->name }}</strong>
                                    <br><small class="text-muted">{{ $cat->slug }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('[name=name]').addEventListener('input', function() {
            document.querySelector('[name=slug]').value = this.value.toLowerCase()
                .replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        });
    </script>
@endsection
