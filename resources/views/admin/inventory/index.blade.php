@extends('admin.layout')
@section('title','Inventory')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Inventory</h3>
    <div class="card p-3">
        @foreach($inventories as $inv)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>{{ $inv->name }}</strong>
                    <div class="muted">SKU: {{ $inv->item_code }} · {{ $inv->category }}</div>
                </div>
                <div>
                    <span class="me-3">Stock: <strong>{{ $inv->quantity_in_stock }}</strong></span>
                    <a href="{{ route('admin.inventory.show', $inv) }}" class="btn btn-sm btn-outline-primary me-2">View</a>
                    <form method="POST" action="{{ route('admin.inventory.destroy', $inv) }}" style="display:inline-block">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
                </div>
            </div>
        @endforeach

        <div>{{ $inventories->links() }}</div>
    </div>
</div>
@endsection
