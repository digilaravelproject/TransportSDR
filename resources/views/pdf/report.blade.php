<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
    </style>
</head>
<body>
    <h1>{{ $report->name }}</h1>
    <p>Type: {{ $report->type }} | Generated: {{ now()->toDateTimeString() }}</p>

    <h3>Summary</h3>
    <ul>
        <li>Total trips: {{ $summary['total_trips'] }}</li>
        <li>Total revenue: {{ number_format($summary['total_revenue'],2) }}</li>
    </ul>

    <h3>Trips</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Trip Number</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trips as $t)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $t->trip_number }}</td>
                <td>{{ $t->trip_date }}</td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ optional($t->vehicle)->registration_number }}</td>
                <td>{{ number_format($t->total_amount,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>