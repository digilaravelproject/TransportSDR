@extends('admin.layout')

@section('title','Leads')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Leads</h3>
        <form class="d-flex" method="GET" action="{{ route('admin.leads.index') }}">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by customer" value="{{ request('search') }}">
            <select name="status" class="form-select me-2">
                <option value="">All</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="row">
        @foreach($leads as $lead)
            <div class="col-12 mb-3">
                <div class="card p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ $lead->customer_name }} <small class="text-muted">#{{ $lead->lead_number }}</small></h5>
                            <div class="small text-muted">{{ $lead->customer_contact }} • {{ $lead->trip_route }} • {{ optional($lead->trip_date)->format('d-m-Y') }}</div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this lead?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $leads->withQueryString()->links() }}
    </div>
</div>
@endsection
