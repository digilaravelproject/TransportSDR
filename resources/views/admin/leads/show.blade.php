@extends('admin.layout')

@section('title','Lead '. $lead->lead_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h3>Lead: {{ $lead->customer_name }} <small class="text-muted">#{{ $lead->lead_number }}</small></h3>
        <div>
            <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3 p-3">
                <h5>Details</h5>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label">Customer Name</label>
                        <input name="customer_name" class="form-control" value="{{ $lead->customer_name }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact</label>
                        <input name="customer_contact" class="form-control" value="{{ $lead->customer_contact }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Trip Route</label>
                        <input name="trip_route" class="form-control" value="{{ $lead->trip_route }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Trip Date</label>
                        <input name="trip_date" type="date" class="form-control" value="{{ optional($lead->trip_date)->toDateString() }}">
                    </div>
                    <button class="btn btn-primary">Save</button>
                </form>
            </div>

            <div class="card mb-3 p-3">
                <h5>Notes</h5>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                    @csrf
                </form>
                <ul class="list-group mt-2">
                    @foreach($lead->notes as $note)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $note->author->name ?? 'Admin' }}</strong>
                                    <div class="small text-muted">{{ $note->created_at->diffForHumans() }}</div>
                                    <div>{{ $note->note }}</div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card mb-3 p-3">
                <h5>Follow Ups</h5>
                <ul class="list-group mt-2">
                    @foreach($lead->followups as $f)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $f->author->name ?? 'Admin' }}</strong>
                                    <div class="small text-muted">{{ $f->reminder_at->format('d-m-Y H:i') }}</div>
                                    <div>{{ $f->note }}</div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>
        <div class="col-md-4">
            <div class="card p-3 mb-3">
                <h6>Assignment</h6>
                <form method="POST" action="{{ route('admin.leads.assign-vehicle', $lead) }}">
                    @csrf
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select mb-2">
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ $lead->vehicle_id == $v->id ? 'selected' : '' }}>{{ $v->registration_number }} ({{ $v->type }})</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary mb-3">Assign Vehicle</button>
                </form>

                <form method="POST" action="{{ route('admin.leads.assign-driver', $lead) }}">
                    @csrf
                    <label class="form-label">Driver</label>
                    <select name="driver_id" class="form-select mb-2">
                        <option value="">-- Select Driver --</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ $lead->driver_id == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->phone }})</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">Assign Driver</button>
                </form>
            </div>

            <div class="card p-3">
                <h6>Expenses</h6>
                <ul class="list-group mt-2">
                    @foreach($lead->expenses as $e)
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <strong>{{ $e->category }}</strong>
                                <div class="small text-muted">{{ $e->description }}</div>
                            </div>
                            <div>
                                <strong>₹{{ $e->amount }}</strong>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
