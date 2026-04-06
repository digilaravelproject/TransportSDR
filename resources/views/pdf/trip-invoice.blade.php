<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
        }

        .label {
            color: #666;
            font-size: 11px;
        }

        .value {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th {
            background: #f0f0f0;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #ccc;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        .total-row {
            background: #f9f9f9;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">{{ $tenant->company_name }}</div>
        @if ($tenant->gstin)
            <div>GSTIN: {{ $tenant->gstin }}</div>
        @endif
        <div>{{ $tenant->phone }} | {{ $tenant->email }}</div>
        <div style="margin-top:8px;font-size:16px;font-weight:bold;">INVOICE</div>
    </div>

    <table style="border:none;margin-bottom:12px;">
        <tr>
            <td style="border:none;width:50%">
                <span class="label">Invoice No:</span> <span class="value">{{ $trip->trip_number }}</span><br>
                <span class="label">Date:</span> {{ $trip->trip_date->format('d-m-Y') }}<br>
                <span class="label">Route:</span> {{ $trip->trip_route }}
            </td>
            <td style="border:none;width:50%;text-align:right;">
                <span class="label">Customer:</span> <span class="value">{{ $trip->customer_name }}</span><br>
                <span class="label">Contact:</span> {{ $trip->customer_contact }}<br>
                <span class="label">Status:</span> {{ ucfirst($trip->payment_status) }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Details</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Trip Charges</td>
                <td>{{ $trip->trip_route }} | {{ $trip->duration_days }} day(s) | {{ $trip->vehicle_type }}
                    x{{ $trip->number_of_vehicles }}</td>
                <td style="text-align:right">₹{{ number_format($trip->total_amount, 2) }}</td>
            </tr>
            @if ($trip->discount > 0)
                <tr>
                    <td colspan="2">Discount</td>
                    <td style="text-align:right">- ₹{{ number_format($trip->discount, 2) }}</td>
                </tr>
            @endif
            @if ($trip->is_gst && $trip->tax_amount > 0)
                <tr>
                    <td colspan="2">GST ({{ $trip->gst_percent }}%)</td>
                    <td style="text-align:right">₹{{ number_format($trip->tax_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="2">Grand Total</td>
                <td style="text-align:right">
                    ₹{{ number_format($trip->total_amount + $trip->tax_amount - $trip->discount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2">Advance Paid</td>
                <td style="text-align:right">₹{{ number_format($trip->advance_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2">Part Payment</td>
                <td style="text-align:right">₹{{ number_format($trip->part_payment, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Balance Due</td>
                <td style="text-align:right">₹{{ number_format($trip->balance_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($trip->total_km)
        <p style="margin-top:12px"><b>KM Details:</b> {{ $trip->start_km }} → {{ $trip->end_km }} =
            {{ $trip->total_km }} km (Grade: {{ $trip->km_grade }})</p>
    @endif

    <div class="footer">
        Thank you for choosing {{ $tenant->company_name }}. This is a computer generated invoice.
    </div>
</body>

</html>
