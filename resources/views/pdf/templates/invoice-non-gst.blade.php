<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 25px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .co {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .title {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            letter-spacing: 2px;
        }

        .section {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .section-title {
            background: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .section-body {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1a1a2e;
            color: #fff;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            background: #f5f5f5;
            font-weight: bold;
            border-top: 2px solid #ccc;
        }

        .net-row td {
            background: #1a1a2e;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }

        .grid td {
            border: none;
            width: 50%;
            vertical-align: top;
            padding: 3px 5px;
        }

        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 6px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="co">{{ $tenant->company_name }}</div>
        <div style="font-size:10px;color:#555;">
            {{ $tenant->address ?? '' }}
            @if (!empty($tenant->phone))
                | {{ $tenant->phone }}
            @endif
            @if (!empty($tenant->email))
                | {{ $tenant->email }}
            @endif
        </div>
        <div class="title">INVOICE</div>
    </div>

    <table class="grid" style="margin-bottom:10px;">
        <tr>
            <td>
                <strong>Invoice No:</strong> {{ $trip->trip_number }}<br>
                <strong>Date:</strong> {{ $trip->trip_date->format('d-m-Y') }}<br>
                <strong>Route:</strong> {{ $trip->trip_route }}<br>
                <strong>Duration:</strong> {{ $trip->duration_days }} Day(s)
            </td>
            <td style="text-align:right;">
                <strong>Bill To:</strong><br>
                <strong>{{ $trip->customer_name }}</strong><br>
                {{ $trip->customer_contact }}
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Service Details</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Vehicle</th>
                    <th>Duration</th>
                    <th class="right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Transport Service — {{ $trip->trip_route }}</td>
                    <td>{{ $trip->vehicle_type }} × {{ $trip->number_of_vehicles }}</td>
                    <td>{{ $trip->duration_days }} Day(s)</td>
                    <td class="right">{{ number_format($trip->total_amount, 2) }}</td>
                </tr>
                @if ($trip->discount > 0)
                    <tr>
                        <td colspan="4">Discount</td>
                        <td class="right">- {{ number_format($trip->discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="net-row">
                    <td colspan="4">TOTAL AMOUNT</td>
                    <td class="right">₹ {{ number_format($trip->total_amount - $trip->discount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td>
                <strong>Advance Paid:</strong> ₹ {{ number_format($trip->advance_amount, 2) }}<br>
                <strong>Part Payment:</strong> ₹ {{ number_format($trip->part_payment, 2) }}<br>
                <strong>Balance Due:</strong> ₹ {{ number_format($trip->balance_amount, 2) }}<br>
                <strong>Status:</strong> {{ ucfirst($trip->payment_status) }}
            </td>
            <td>
                @if ($trip->driver)
                    <strong>Driver:</strong> {{ $trip->driver->name }}<br>
                    <strong>Contact:</strong> {{ $trip->driver->phone }}
                @endif
            </td>
        </tr>
    </table>

    @if ($trip->notes)
        <div style="margin-top:8px;padding:6px 8px;background:#f9f9f9;border-radius:4px;font-size:10px;">
            <strong>Notes:</strong> {{ $trip->notes }}
        </div>
    @endif

    <table style="margin-top:25px;">
        <tr>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">Authorized
                    Signature</div>
            </td>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">Customer
                    Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This is a computer generated Invoice. | {{ $tenant->company_name }} | Generated:
        {{ now()->format('d-m-Y H:i') }}
    </div>
</body>

</html>
