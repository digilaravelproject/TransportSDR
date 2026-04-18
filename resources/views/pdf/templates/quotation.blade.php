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

        .validity-box {
            background: #fff8e1;
            border: 1px solid #f9a825;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 10px;
            margin-bottom: 10px;
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
            @if (!empty($tenant->gstin))
                | GSTIN: {{ $tenant->gstin }}
            @endif
        </div>
        <div class="title">QUOTATION</div>
    </div>

    <table class="grid" style="margin-bottom:10px;">
        <tr>
            <td>
                <strong>Quotation No:</strong> {{ $number }}<br>
                <strong>Date:</strong> {{ $date }}<br>
                <strong>Valid Till:</strong> {{ $valid_till }}
            </td>
            <td style="text-align:right;">
                <strong>Prepared For:</strong><br>
                <strong>{{ $customer['name'] }}</strong><br>
                {{ $customer['contact'] }}<br>
                @if (!empty($customer['email']))
                    {{ $customer['email'] }}<br>
                @endif
                @if (!empty($customer['gstin']))
                    GSTIN: {{ $customer['gstin'] }}
                @endif
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Trip Details</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td><strong>Route:</strong> {{ $trip['route'] }}</td>
                    <td><strong>Trip Date:</strong> {{ $trip['date'] }}</td>
                </tr>
                <tr>
                    <td><strong>Return Date:</strong> {{ $trip['return_date'] ?? 'Same Day' }}</td>
                    <td><strong>Duration:</strong> {{ $trip['duration'] }} Day(s)</td>
                </tr>
                <tr>
                    <td><strong>Pickup:</strong> {{ $trip['pickup_address'] }}</td>
                    <td>
                        <strong>Destinations:</strong>
                        @foreach ($trip['destinations'] ?? [] as $dest)
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
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Vehicle</th>
                    <th>Seats</th>
                    <th>Qty</th>
                    <th class="right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Transport Service</td>
                    <td>{{ $trip['vehicle_type'] }}</td>
                    <td>{{ $trip['seating_capacity'] }}</td>
                    <td>{{ $trip['number_of_vehicles'] }}</td>
                    <td class="right">{{ number_format($pricing['amount'], 2) }}</td>
                </tr>
                @if ($pricing['discount'] > 0)
                    <tr>
                        <td colspan="4">Discount</td>
                        <td class="right">- {{ number_format($pricing['discount'], 2) }}</td>
                    </tr>
                @endif
                @if ($pricing['is_gst'] && $pricing['tax_amount'] > 0)
                    <tr>
                        <td colspan="4">GST ({{ $pricing['gst_percent'] }}%)</td>
                        <td class="right">{{ number_format($pricing['tax_amount'], 2) }}</td>
                    </tr>
                @endif
                <tr class="net-row">
                    <td colspan="4">TOTAL AMOUNT</td>
                    <td class="right">₹ {{ number_format($pricing['total_with_tax'], 2) }}</td>
                </tr>
                @if ($pricing['advance_required'] > 0)
                    <tr class="total-row">
                        <td colspan="4">Advance Required</td>
                        <td class="right">₹ {{ number_format($pricing['advance_required'], 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if (!empty($notes))
        <div style="margin-bottom:10px;padding:6px 8px;background:#f9f9f9;border-radius:4px;font-size:10px;">
            <strong>Notes:</strong> {{ $notes }}
        </div>
    @endif

    <div class="validity-box">
        <strong>This quotation is valid till {{ $valid_till }}.</strong>
        To confirm booking, please pay the advance amount. Prices may change after validity period.
    </div>

    <table style="margin-top:25px;">
        <tr>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">Customer
                    Signature</div>
            </td>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">Authorized
                    Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Thank you for your enquiry. | {{ $tenant->company_name }} | Generated: {{ now()->format('d-m-Y H:i') }}
    </div>
</body>

</html>
