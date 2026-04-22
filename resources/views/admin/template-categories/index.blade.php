@extends('admin.layout')
@section('title', 'Template Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags me-2"></i>Template Categories</h2>
        <a href="{{ route('admin.template-categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Category
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Name</th>
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
                                <i class="{{ $cat->icon ?? 'fas fa-file' }}"
                                    style="color: {{ $cat->color }}; font-size: 1.2rem;"></i>
                            </td>
                            <td>
                                <strong>{{ $cat->name }}</strong>
                                @if ($cat->description)
                                    <br><small class="text-muted">{{ Str::limit($cat->description, 50) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $cat->slug }}</code></td>
                            <td>
                                <span class="badge bg-info">{{ $cat->templates_count }} templates</span>
                            </td>
                            <td>
                                @if ($cat->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $cat->sort_order }}</td>
                            <td>
                                <a href="{{ route('admin.template-categories.show', $cat->id) }}"
                                    class="btn btn-sm btn-outline-info me-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.template-categories.edit', $cat->id) }}"
                                    class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.template-categories.destroy', $cat->id) }}"
                                    style="display:inline;" onsubmit="return confirm('Delete this category?')">
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
                                No categories found.
                                <a href="{{ route('admin.template-categories.create') }}">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $categories->links('pagination::bootstrap-4') }}</div>
@endsection
