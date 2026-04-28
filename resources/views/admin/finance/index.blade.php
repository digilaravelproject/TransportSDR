@extends('admin.layout')
@section('title','Finance')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Finance - Cashbook</h3>
    <div class="card p-3">
        @foreach($entries as $e)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>{{ ucfirst($e->category) }}</strong>
                    <div class="muted">{{ $e->entry_date?->format('Y-m-d') }} · {{ ucfirst($e->payment_mode) }}</div>
                </div>
                <div>
                    <a href="{{ route('admin.finance.show', $e) }}" class="btn btn-sm btn-outline-primary me-2">View</a>
                    <form method="POST" action="{{ route('admin.finance.destroy', $e) }}" style="display:inline-block">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
                </div>
            </div>
        @endforeach

        <div>{{ $entries->links() }}</div>
    </div>
</div>
@endsection
