@extends('admin.layout')
@section('title', 'Add Module')
@section('content')
<div class="container mt-4">
    <h2>Add Module</h2>
    <form action="{{ route('admin.modules.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Module Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
