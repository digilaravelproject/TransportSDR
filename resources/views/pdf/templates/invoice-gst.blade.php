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
            color: #1a1a2e;
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

        .bold {
            font-weight: bold;
        }

        .total-row td {
            background: #f5f5f5;
            font-weight: bold;
            border-top: 2px solid #ccc;
        }

        .gst-row td {
            background: #f0fff0;
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

        .irn-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 4px;
            font-size: 9px;
            margin-bottom: 10px;
            word-break: break-all;
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

    {{-- IRN Block (e-invoice only) --}}
    @if (!empty($einvoice['irn']))
        <div class="irn-box">
            <strong>E-Invoice Details</strong><br>
            <strong>IRN:</strong> {{ $einvoice['irn'] }}<br>
            <strong>Ack No:</strong> {{ $einvoice['ack_number'] }}
            &nbsp;|&nbsp; <strong>Ack Date:</strong> {{ $einvoice['ack_date'] }}
            @if (!empty($einvoice['qr_url']))
                <div style="float:right;margin-top:-30px;">
                    <img src="{{ $einvoice['qr_url'] }}" width="70" height="70">
                </div>
            @endif
        </div>
    @endif

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
        @if (!empty($tenant->gstin))
            <div style="font-size:10px;"><strong>GSTIN:</strong> {{ $tenant->gstin }}</div>
        @endif
        <div class="title">TAX INVOICE</div>
    </div>

    <table class="grid" style="margin-bottom:10px;">
        <tr>
            <td>
                <strong>Invoice No:</strong> {{ $trip->trip_number }}<br>
                <strong>Date:</strong> {{ $trip->trip_date->format('d-m-Y') }}<br>
                <strong>Trip Route:</strong> {{ $trip->trip_route }}<br>
                <strong>Duration:</strong> {{ $trip->duration_days }} Day(s)
            </td>
            <td style="text-align:right;">
                <strong>Bill To:</strong><br>
                <strong>{{ $trip->customer_name }}</strong><br>
                {{ $trip->customer_contact }}<br>
                @if (isset($trip->customer) && is_array($trip->customer) && isset($trip->customer['gstin']))
                    <strong>GSTIN:</strong> {{ $trip->customer['gstin'] }}
                @elseif (isset($trip->customer->gstin))
                    <strong>GSTIN:</strong> {{ $trip->customer->gstin }}
                @endif
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
                    <th>HSN</th>
                    <th>Vehicle</th>
                    <th class="right">Taxable Amt (₹)</th>
                    <th class="right">GST %</th>
                    <th class="right">Tax (₹)</th>
                    <th class="right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Transport Service<br><small>{{ $trip->trip_route }} | {{ $trip->duration_days }}
                            day(s)</small></td>
                    <td>996421</td>
                    <td>{{ $trip->vehicle_type }} × {{ $trip->number_of_vehicles }}</td>
                    <td class="right">{{ number_format($gst['taxable_amount'], 2) }}</td>
                    <td class="right">{{ $gst['gst_percent'] }}%</td>
                    <td class="right">{{ number_format($gst['total_tax'], 2) }}</td>
                    <td class="right">{{ number_format($gst['grand_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Tax Breakdown</div>
        <div class="section-body">
            <table>
                <tr>
                    <td>Taxable Amount</td>
                    <td class="right">₹ {{ number_format($gst['taxable_amount'], 2) }}</td>
                </tr>
                @if ($trip->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td class="right">- ₹ {{ number_format($trip->discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="gst-row">
                    <td>CGST @ {{ $gst['cgst_percent'] }}%</td>
                    <td class="right">₹ {{ number_format($gst['cgst'], 2) }}</td>
                </tr>
                <tr class="gst-row">
                    <td>SGST @ {{ $gst['sgst_percent'] }}%</td>
                    <td class="right">₹ {{ number_format($gst['sgst'], 2) }}</td>
                </tr>
                @if ($gst['igst'] > 0)
                    <tr class="gst-row">
                        <td>IGST @ {{ $gst['gst_percent'] }}%</td>
                        <td class="right">₹ {{ number_format($gst['igst'], 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Total Tax</td>
                    <td class="right">₹ {{ number_format($gst['total_tax'], 2) }}</td>
                </tr>
                <tr class="net-row">
                    <td>GRAND TOTAL</td>
                    <td class="right">₹ {{ number_format($gst['grand_total'], 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <strong>Advance Paid:</strong> ₹ {{ number_format($trip->advance_amount, 2) }}<br>
                <strong>Part Payment:</strong> ₹ {{ number_format($trip->part_payment, 2) }}<br>
                <strong>Balance Due:</strong> ₹ {{ number_format($trip->balance_amount, 2) }}<br>
                <strong>Payment Status:</strong> {{ ucfirst($trip->payment_status) }}
            </td>
            <td>
                @if (isset($trip->start_km) && isset($trip->end_km))
                    <strong>KM Details:</strong><br>
                    Start: {{ number_format($trip->start_km, 0) }} | End: {{ number_format($trip->end_km, 0) }}<br>
                    Total: {{ number_format($trip->total_km ?? 0, 0) }} km (Grade: {{ $trip->km_grade ?? 'N/A' }})
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
        This is a computer generated Tax Invoice. | {{ $tenant->company_name }} | Generated:
        {{ now()->format('d-m-Y H:i') }}
    </div>
</body>

</html>
