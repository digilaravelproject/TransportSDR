<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .company-info {
            font-size: 11px;
            color: #555;
            margin-top: 3px;
        }

        .slip-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-top: 8px;
            background: #333;
            color: #fff;
            display: inline-block;
            padding: 3px 20px;
        }

        .slip-number {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        .section {
            border: 1px solid #ccc;
            margin-bottom: 10px;
            border-radius: 4px;
            overflow: hidden;
        }

        .section-title {
            background: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #ccc;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-body {
            padding: 10px;
        }

        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-2 td {
            width: 50%;
            padding: 5px 6px;
            vertical-align: top;
        }

        .grid-3 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-3 td {
            width: 33.33%;
            padding: 5px 6px;
            vertical-align: top;
        }

        .field-label {
            font-size: 10px;
            color: #888;
            margin-bottom: 2px;
        }

        .field-value {
            font-size: 12px;
            font-weight: bold;
            color: #222;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-scheduled {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-ongoing {
            background: #fff8e1;
            color: #e65100;
        }

        .badge-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-cancelled {
            background: #ffebee;
            color: #b71c1c;
        }

        .destinations {
            margin: 0;
            padding-left: 16px;
        }

        .destinations li {
            margin-bottom: 2px;
            font-size: 11px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table td {
            padding: 5px 8px;
            font-size: 11px;
            border-bottom: 1px solid #eee;
        }

        .payment-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .payment-table tr:last-child td {
            border-bottom: none;
        }

        .balance-row td {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #ccc !important;
        }

        .km-box {
            width: 100%;
            border-collapse: collapse;
        }

        .km-box td {
            padding: 5px 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .km-box th {
            padding: 5px 8px;
            text-align: center;
            background: #f0f0f0;
            border: 1px solid #ddd;
            font-size: 10px;
            font-weight: bold;
        }

        .signature-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .signature-section td {
            width: 33.33%;
            text-align: center;
            padding: 0 10px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
            color: #555;
        }

        .notes-box {
            background: #fffde7;
            border: 1px dashed #f9a825;
            padding: 8px 10px;
            font-size: 11px;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        .divider {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 8px 0;
        }

        .highlight {
            background: #fff3e0;
            padding: 2px 6px;
            border-radius: 2px;
        }
    </style>
</head>

<body>

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <div class="company-name">{{ $tenant->company_name ?? 'Company Name' }}</div>
        <div class="company-info">
            @if (!empty($tenant->address))
                {{ $tenant->address }} |
            @endif
            {{ $tenant->phone ?? '' }}
            @if (!empty($tenant->email))
                | {{ $tenant->email }}
            @endif
            @if (!empty($tenant->gstin))
                | GSTIN: {{ $tenant->gstin }}
            @endif
        </div>
        <div style="margin-top: 8px;">
            <span class="slip-title">DUTY SLIP</span>
        </div>
        <div class="slip-number">
            Trip No: <strong>{{ $trip->trip_number }}</strong>
            &nbsp;|&nbsp;
            Date: <strong>{{ $trip->trip_date?->format('d-m-Y') }}</strong>
            &nbsp;|&nbsp;
            Status:
            <span class="badge badge-{{ $trip->status }}">
                {{ strtoupper($trip->status) }}
            </span>
        </div>
    </div>

    {{-- ===== TRIP DETAILS ===== --}}
    <div class="section">
        <div class="section-title">Trip Details</div>
        <div class="section-body">
            <table class="grid-2">
                <tr>
                    <td>
                        <div class="field-label">Trip Date</div>
                        <div class="field-value">{{ $trip->trip_date?->format('d-m-Y') }}</div>
                    </td>
                    <td>
                        <div class="field-label">Return Date</div>
                        <div class="field-value">
                            {{ $trip->return_date?->format('d-m-Y') ?? 'Same Day' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Duration</div>
                        <div class="field-value">{{ $trip->duration_days }} Day(s)</div>
                    </td>
                    <td>
                        <div class="field-label">Route</div>
                        <div class="field-value">{{ $trip->trip_route }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Pickup Address</div>
                        <div class="field-value">{{ $trip->pickup_address }}</div>
                    </td>
                    <td>
                        <div class="field-label">Destination Points</div>
                        <div class="field-value">
                            @if (!empty($trip->destination_points))
                                <ul class="destinations">
                                    @foreach ($trip->destination_points as $dest)
                                        <li>{{ $dest }}</li>
                                    @endforeach
                                </ul>
                            @else
                                —
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== VEHICLE DETAILS ===== --}}
    <div class="section">
        <div class="section-title">Vehicle Details</div>
        <div class="section-body">
            <table class="grid-3">
                <tr>
                    <td>
                        <div class="field-label">Vehicle Type</div>
                        <div class="field-value">{{ $trip->vehicle_type }}</div>
                    </td>
                    <td>
                        <div class="field-label">Registration No</div>
                        <div class="field-value">
                            {{ $trip->vehicle?->registration_number ?? '—' }}
                        </div>
                    </td>
                    <td>
                        <div class="field-label">Seating Capacity</div>
                        <div class="field-value">{{ $trip->seating_capacity }} Seats</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Make / Model</div>
                        <div class="field-value">
                            {{ $trip->vehicle?->make ?? '—' }}
                            {{ $trip->vehicle?->model ? '/ ' . $trip->vehicle->model : '' }}
                        </div>
                    </td>
                    <td>
                        <div class="field-label">Number of Vehicles</div>
                        <div class="field-value">{{ $trip->number_of_vehicles }}</div>
                    </td>
                    <td>
                        <div class="field-label">Fuel Type</div>
                        <div class="field-value">
                            {{ ucfirst($trip->vehicle?->fuel_type ?? '—') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== DRIVER & HELPER ===== --}}
    <div class="section">
        <div class="section-title">Assigned Staff</div>
        <div class="section-body">
            <table class="grid-2">
                <tr>
                    <td>
                        <div class="field-label">Driver Name</div>
                        <div class="field-value">
                            {{ $trip->driver?->name ?? 'Not Assigned' }}
                        </div>
                        @if ($trip->driver?->phone)
                            <div style="font-size:11px; color:#555; margin-top:2px;">
                                {{ $trip->driver->phone }}
                            </div>
                        @endif
                        @if ($trip->driver?->license_number)
                            <div class="field-label" style="margin-top:4px;">License No</div>
                            <div style="font-size:11px;">
                                {{ $trip->driver->license_number }}
                                @if ($trip->driver?->license_expiry)
                                    (Exp: {{ $trip->driver->license_expiry->format('d-m-Y') }})
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="field-label">Helper Name</div>
                        <div class="field-value">
                            {{ $trip->helper?->name ?? 'Not Assigned' }}
                        </div>
                        @if ($trip->helper?->phone)
                            <div style="font-size:11px; color:#555; margin-top:2px;">
                                {{ $trip->helper->phone }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== CUSTOMER DETAILS ===== --}}
    <div class="section">
        <div class="section-title">Customer Details</div>
        <div class="section-body">
            <table class="grid-3">
                <tr>
                    <td>
                        <div class="field-label">Customer Name</div>
                        <div class="field-value">{{ $trip->customer_name }}</div>
                    </td>
                    <td>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value">{{ $trip->customer_contact }}</div>
                    </td>
                    <td>
                        <div class="field-label">Customer Email</div>
                        <div class="field-value">
                            {{ $trip->customer?->email ?? '—' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== KM TRACKING ===== --}}
    <div class="section">
        <div class="section-title">KM Tracking</div>
        <div class="section-body">
            <table class="km-box">
                <thead>
                    <tr>
                        <th>Start KM</th>
                        <th>End KM</th>
                        <th>Total KM</th>
                        <th>KM Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $trip->start_km ? number_format($trip->start_km, 0) : '________________' }}
                        </td>
                        <td>
                            {{ $trip->end_km ? number_format($trip->end_km, 0) : '________________' }}
                        </td>
                        <td>
                            {{ $trip->total_km ? number_format($trip->total_km, 0) . ' km' : '________________' }}
                        </td>
                        <td>
                            @if ($trip->km_grade)
                                <span class="highlight">{{ $trip->km_grade }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== PAYMENT SUMMARY ===== --}}
    <div class="section">
        <div class="section-title">Payment Summary</div>
        <div class="section-body">
            <table class="payment-table">
                <tr>
                    <td>Total Amount</td>
                    <td>₹ {{ number_format($trip->total_amount, 2) }}</td>
                </tr>
                @if ($trip->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td>- ₹ {{ number_format($trip->discount, 2) }}</td>
                    </tr>
                @endif
                @if ($trip->is_gst && $trip->tax_amount > 0)
                    <tr>
                        <td>GST ({{ $trip->gst_percent }}%)</td>
                        <td>₹ {{ number_format($trip->tax_amount, 2) }}</td>
                    </tr>
                @endif
                @if ($trip->advance_amount > 0)
                    <tr>
                        <td>Advance Paid</td>
                        <td>- ₹ {{ number_format($trip->advance_amount, 2) }}</td>
                    </tr>
                @endif
                @if ($trip->part_payment > 0)
                    <tr>
                        <td>Part Payment</td>
                        <td>- ₹ {{ number_format($trip->part_payment, 2) }}</td>
                    </tr>
                @endif
                <tr class="balance-row">
                    <td>Balance Due</td>
                    <td>₹ {{ number_format($trip->balance_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== NOTES ===== --}}
    @if (!empty($trip->notes))
        <div class="notes-box">
            <strong>Notes:</strong> {{ $trip->notes }}
        </div>
    @endif

    {{-- ===== SIGNATURES ===== --}}
    <table class="signature-section">
        <tr>
            <td>
                <div class="signature-line">Driver Signature</div>
            </td>
            <td>
                <div class="signature-line">Customer Signature</div>
            </td>
            <td>
                <div class="signature-line">Authorized Signature</div>
            </td>
        </tr>
    </table>

    {{-- ===== FOOTER ===== --}}
    <div class="footer">
        This is a computer generated duty slip.
        {{ $tenant->company_name ?? '' }}
        @if (!empty($tenant->phone))
            | {{ $tenant->phone }}
        @endif
        @if (!empty($tenant->email))
            | {{ $tenant->email }}
        @endif
        &nbsp;|&nbsp; Generated on: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>

</html>
