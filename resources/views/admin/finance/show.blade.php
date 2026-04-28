@extends('admin.layout')
@section('title','Finance Details')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Finance Entry #{{ $entry->id }}</h3>
    <div class="card p-4">
        <p><strong>Type:</strong> {{ ucfirst($entry->entry_type) }}</p>
        <p><strong>Amount:</strong> ₹{{ number_format($entry->amount,2) }}</p>
        <p><strong>Category:</strong> {{ $entry->category }}</p>
        <p><strong>Payment Method:</strong> {{ $entry->payment_mode }}</p>
        <p><strong>Reference:</strong> {{ $entry->reference_number }}</p>
        <p><strong>Description:</strong> {{ $entry->description }}</p>
        @if($entry->receipt_path)
            <p><a href="{{ asset('storage/' . $entry->receipt_path) }}" class="btn btn-sm btn-primary">Download Receipt</a></p>
        @endif
    </div>
</div>
@endsection
