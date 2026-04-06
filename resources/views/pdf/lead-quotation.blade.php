<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 30px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 6px;
            color: #1a1a2e;
        }

        .section {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .section-title {
            background: #f0f0f0;
            padding: 6px 10px;
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
            padding: 7px 8px;
            font-size: 11px;
            text-align: left;
        }

        td {
            padding: 7px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .total-row td {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #ccc;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        .validity {
            background: #fff8e1;
            border: 1px solid #f9a825;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 11px;
            margin-top: 10px;
        }

        .grid td {
            border: none;
            width: 50%;
            vertical-align: top;
            padding: 4px 6px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="company">{{ $tenant->company_name ?? 'Company' }}</div>
        @if (!empty($tenant->address))
            <div style="font-size:11px;color:#555;">{{ $tenant->address }}</div>
        @endif
        <div style="font-size:11px;color:#555;">
            {{ $tenant->phone ?? '' }}
            @if (!empty($tenant->email))
                | {{ $tenant->email }}
            @endif
            @if (!empty($tenant->gstin))
                | GSTIN: {{ $tenant->gstin }}
            @endif
        </div>
        <div class="title">QUOTATION</div>
    </div>

    <table class="grid" style="margin-bottom:12px;">
        <tr>
            <td>
                <strong>Quotation No:</strong> {{ $lead->lead_number }}<br>
                <strong>Date:</strong> {{ $lead->enquiry_date?->format('d-m-Y') }}<br>
                <strong>Valid Until:</strong> {{ $lead->enquiry_date?->addDays(7)->format('d-m-Y') }}
            </td>
            <td style="text-align:right;">
                <strong>Customer:</strong> {{ $lead->customer_name }}<br>
                <strong>Contact:</strong> {{ $lead->customer_contact }}<br>
                @if (!empty($lead->customer_email))
                    <strong>Email:</strong> {{ $lead->customer_email }}
                @endif
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Trip Details</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td><strong>Route:</strong> {{ $lead->trip_route }}</td>
                    <td><strong>Trip Date:</strong> {{ $lead->trip_date?->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Duration:</strong> {{ $lead->duration_days }} Day(s)</td>
                    <td><strong>Return Date:</strong> {{ $lead->return_date?->format('d-m-Y') ?? 'Same Day' }}</td>
                </tr>
                <tr>
                    <td><strong>Pickup:</strong> {{ $lead->pickup_address }}</td>
                    <td>
                        <strong>Destinations:</strong>
                        @foreach ($lead->destination_points ?? [] as $dest)
                            {{ $dest }}@if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Vehicle & Pricing</div>
        <div class="section-body">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Vehicle Type</th>
                        <th>Seats</th>
                        <th>Qty</th>
                        <th class="right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Trip Charges — {{ $lead->trip_route }}</td>
                        <td>{{ $lead->vehicle_type }}</td>
                        <td>{{ $lead->seating_capacity }}</td>
                        <td>{{ $lead->number_of_vehicles }}</td>
                        <td class="right">{{ number_format($lead->quoted_amount, 2) }}</td>
                    </tr>
                    @if ($lead->discount > 0)
                        <tr>
                            <td colspan="4">Discount</td>
                            <td class="right">- {{ number_format($lead->discount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($lead->is_gst && $lead->tax_amount > 0)
                        <tr>
                            <td colspan="4">GST ({{ $lead->gst_percent }}%)</td>
                            <td class="right">{{ number_format($lead->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="4">Total Amount</td>
                        <td class="right">₹ {{ number_format($lead->total_with_tax, 2) }}</td>
                    </tr>
                    @if ($lead->advance_amount > 0)
                        <tr>
                            <td colspan="4">Advance Required</td>
                            <td class="right">₹ {{ number_format($lead->advance_amount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @if (!empty($lead->notes))
        <div style="margin-bottom:10px;padding:8px 10px;background:#f9f9f9;border-radius:4px;font-size:11px;">
            <strong>Notes:</strong> {{ $lead->notes }}
        </div>
    @endif

    <div class="validity">
        This quotation is valid for 7 days from the date of issue.
        For booking confirmation, please pay the advance amount.
    </div>

    <table style="margin-top:30px;">
        <tr>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:40px;padding-top:4px;font-size:11px;">
                    Customer Signature
                </div>
            </td>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:40px;padding-top:4px;font-size:11px;">
                    Authorized Signature
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Thank you for your enquiry. We look forward to serving you.<br>
        {{ $tenant->company_name ?? '' }}
        @if (!empty($tenant->phone))
            | {{ $tenant->phone }}
        @endif
        | Generated: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>

</html>
