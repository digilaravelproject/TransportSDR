@extends('admin.layout')
@section('title','Inventory Detail')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Inventory: {{ $inventory->name }}</h3>
    <div class="card p-4">
        <p><strong>SKU:</strong> {{ $inventory->item_code }}</p>
        <p><strong>Category:</strong> {{ $inventory->category }}</p>
        <p><strong>Stock:</strong> {{ $inventory->quantity_in_stock }}</p>
        <p><strong>Reorder Level:</strong> {{ $inventory->reorder_level }}</p>
        <p><strong>Unit Price:</strong> ₹{{ number_format($inventory->unit_price,2) }}</p>
        <p><strong>Location:</strong> {{ $inventory->storage_location }}</p>
        <p><strong>Description:</strong> {{ $inventory->description }}</p>

        <h5 class="mt-4">Recent Activity</h5>
        <ul>
            @foreach($inventory->stocks()->latest()->take(10)->get() as $s)
                <li>{{ ucfirst($s->transaction_type) }} {{ $s->quantity }} on {{ $s->transaction_date }} — {{ $s->reason }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
