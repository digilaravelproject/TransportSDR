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

        .company {
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

        .center {
            text-align: center;
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
            background: #fff8e1;
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

        .badge-paid {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-pend {
            background: #fff8e1;
            color: #e65100;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
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

    {{-- Header --}}
    <div class="header">
        <div class="company">{{ $tenant->company_name ?? 'Company' }}</div>
        <div style="font-size:10px;color:#555;">
            {{ $tenant->phone ?? '' }}
            @if (!empty($tenant->email))
                | {{ $tenant->email }}
            @endif
            @if (!empty($tenant->gstin))
                | GSTIN: {{ $tenant->gstin }}
            @endif
            @if (!empty($tenant->address))
                | {{ $tenant->address }}
            @endif
        </div>
        <div class="title">
            @if ($payment->is_gst)
                TAX INVOICE
            @else
                INVOICE
            @endif
        </div>
    </div>

    {{-- Invoice info --}}
    <table class="grid" style="margin-bottom:10px;">
        <tr>
            <td>
                <strong>Invoice No:</strong> {{ $payment->invoice_number }}<br>
                <strong>Date:</strong> {{ now()->format('d-m-Y') }}<br>
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($payment->billing_from)->format('d-m-Y') }}
                to {{ \Carbon\Carbon::parse($payment->billing_to)->format('d-m-Y') }}<br>
                <strong>Status:</strong>
                @if ($payment->payment_status === 'paid')
                    <span class="badge-paid">PAID</span>
                @else
                    <span class="badge-pend">{{ strtoupper($payment->payment_status) }}</span>
                @endif
            </td>
            <td style="text-align:right;">
                <strong>Bill To:</strong><br>
                <span style="font-size:13px;font-weight:bold;">{{ $corporate->company_name }}</span><br>
                {{ $corporate->contact_person ?? '' }}<br>
                {{ $corporate->phone }}<br>
                @if (!empty($corporate->gstin))
                    GSTIN: {{ $corporate->gstin }}
                @endif
                <br>
                @if (!empty($corporate->address))
                    {{ $corporate->address }}
                @endif
            </td>
        </tr>
    </table>

    {{-- Duty Summary --}}
    <div class="section">
        <div class="section-title">Duty Summary</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td><strong>Total Duties:</strong> {{ $payment->total_duties }}</td>
                    <td><strong>Holiday Duties:</strong> {{ $payment->holiday_duties }}</td>
                </tr>
                <tr>
                    <td><strong>Extra Duties:</strong> {{ $payment->extra_duties }}</td>
                    <td><strong>Total KM:</strong> {{ number_format($payment->total_km, 2) }} km</td>
                </tr>
                <tr>
                    <td><strong>Extra KM:</strong> {{ number_format($payment->extra_km, 2) }} km</td>
                    <td><strong>Contract Type:</strong> {{ ucfirst($corporate->contract_type) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Duty Details --}}
    @if ($duties->count() > 0)
        <div class="section">
            <div class="section-title">Duty Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duty No</th>
                        <th>Type</th>
                        <th>Driver</th>
                        <th class="right">KM</th>
                        <th class="right">Extra KM</th>
                        <th class="right">Holiday</th>
                        <th class="right">Fine</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($duties as $duty)
                        <tr>
                            <td>{{ $duty->duty_date->format('d-m-Y') }}</td>
                            <td>{{ $duty->duty_number }}</td>
                            <td>{{ ucfirst($duty->duty_type) }}
                                @if ($duty->is_holiday)
                                    <span style="color:#e65100;">(Holiday)</span>
                                @endif
                                @if ($duty->is_extra_duty)
                                    <span style="color:#1565c0;">(Extra)</span>
                                @endif
                            </td>
                            <td>{{ $duty->driver?->name ?? '—' }}</td>
                            <td class="right">{{ number_format($duty->total_km ?? 0, 0) }}</td>
                            <td class="right">{{ number_format($duty->extra_km ?? 0, 0) }}</td>
                            <td class="right">{{ $duty->is_holiday ? '✓' : '—' }}</td>
                            <td class="right">
                                {{ $duty->fine_amount > 0 ? '₹' . number_format($duty->fine_amount, 0) : '—' }}</td>
                            <td class="right">₹ {{ number_format($duty->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Billing Breakdown --}}
    <div class="section">
        <div class="section-title">Billing Breakdown</div>
        <div class="section-body">
            <table>
                @if ($corporate->contract_type === 'monthly')
                    <tr>
                        <td>Monthly Package</td>
                        <td class="right">₹ {{ number_format($payment->base_amount, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Base Amount ({{ $payment->total_duties }} duties ×
                            ₹{{ number_format($corporate->per_day_rate, 2) }})</td>
                        <td class="right">₹ {{ number_format($payment->base_amount, 2) }}</td>
                    </tr>
                @endif

                @if ($payment->extra_km_amount > 0)
                    <tr>
                        <td>Extra KM Charges ({{ number_format($payment->extra_km, 2) }} km ×
                            ₹{{ $corporate->per_km_rate }})</td>
                        <td class="right">₹ {{ number_format($payment->extra_km_amount, 2) }}</td>
                    </tr>
                @endif

                @if ($payment->extra_hour_amount > 0)
                    <tr>
                        <td>Extra Hour Charges</td>
                        <td class="right">₹ {{ number_format($payment->extra_hour_amount, 2) }}</td>
                    </tr>
                @endif

                @if ($payment->holiday_amount > 0)
                    <tr>
                        <td>Holiday Duty Charges ({{ $payment->holiday_duties }} duties)</td>
                        <td class="right">₹ {{ number_format($payment->holiday_amount, 2) }}</td>
                    </tr>
                @endif

                @if ($payment->extra_duty_amount > 0)
                    <tr>
                        <td>Extra Duty Charges ({{ $payment->extra_duties }} duties)</td>
                        <td class="right">₹ {{ number_format($payment->extra_duty_amount, 2) }}</td>
                    </tr>
                @endif

                @if ($payment->fine_deduction > 0)
                    <tr>
                        <td>Fine Deduction</td>
                        <td class="right">- ₹ {{ number_format($payment->fine_deduction, 2) }}</td>
                    </tr>
                @endif

                <tr class="total-row">
                    <td>Subtotal</td>
                    <td class="right">₹ {{ number_format($payment->subtotal, 2) }}</td>
                </tr>

                @if ($payment->is_gst && $payment->tax_amount > 0)
                    <tr class="gst-row">
                        <td>CGST ({{ $payment->gst_percent / 2 }}%)</td>
                        <td class="right">₹ {{ number_format($payment->cgst, 2) }}</td>
                    </tr>
                    <tr class="gst-row">
                        <td>SGST ({{ $payment->gst_percent / 2 }}%)</td>
                        <td class="right">₹ {{ number_format($payment->sgst, 2) }}</td>
                    </tr>
                @endif

                <tr class="net-row">
                    <td>TOTAL AMOUNT</td>
                    <td class="right">₹ {{ number_format($payment->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Payment info --}}
    @if ($payment->payment_status === 'paid')
        <table class="grid" style="margin-bottom:10px;">
            <tr>
                <td><strong>Paid On:</strong> {{ $payment->paid_on?->format('d-m-Y') }}</td>
                <td><strong>Mode:</strong> {{ ucfirst($payment->payment_mode) }}</td>
            </tr>
            @if ($payment->transaction_ref)
                <tr>
                    <td colspan="2"><strong>Ref:</strong> {{ $payment->transaction_ref }}</td>
                </tr>
            @endif
        </table>
    @else
        <div
            style="background:#fff8e1;border:1px solid #f9a825;padding:8px 10px;border-radius:4px;margin-bottom:10px;font-size:10px;">
            <strong>Balance Due: ₹ {{ number_format($payment->balance_amount, 2) }}</strong>
            — Please pay within 7 days of invoice date.
        </div>
    @endif

    <table style="margin-top:25px;">
        <tr>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">
                    Authorized Signature
                </div>
            </td>
            <td style="text-align:center;width:50%;">
                <div style="border-top:1px solid #333;margin-top:35px;padding-top:4px;font-size:10px;">
                    Company Stamp &amp; Signature
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        @if ($payment->is_gst)
            This is a computer generated Tax Invoice.
        @else
            This is a computer generated Invoice.
        @endif
        {{ $tenant->company_name ?? '' }} | Generated: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>

</html>
