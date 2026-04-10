
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body  { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; margin: 30px; }
  .header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 10px; margin-bottom: 14px; }
  .company { font-size: 20px; font-weight: bold; color: #1a1a2e; }
  .title   { font-size: 15px; font-weight: bold; margin-top: 6px; letter-spacing: 2px; }
  table    { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  th       { background: #1a1a2e; color: #fff; padding: 7px 8px; font-size: 11px; text-align: left; }
  td       { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
  .right   { text-align: right; }
  .section { border: 1px solid #ddd; border-radius: 4px; margin-bottom: 12px; overflow: hidden; }
  .section-title { background: #f0f0f0; padding: 5px 10px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
  .section-body  { padding: 10px; }
  .total-row td  { background: #f5f5f5; font-weight: bold; border-top: 2px solid #ccc; }
  .net-row td    { background: #1a1a2e; color: #fff; font-weight: bold; font-size: 14px; }
  .grid td       { border: none; width: 50%; vertical-align: top; padding: 3px 6px; }
  .paid-badge    { background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
  .pending-badge { background: #fff8e1; color: #e65100; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
  .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
  <div class="company">{{ $tenant->company_name ?? 'Company' }}</div>
  <div style="font-size:11px;color:#555;">
    {{ $tenant->phone ?? '' }}
    @if(!empty($tenant->email)) | {{ $tenant->email }}@endif
    @if(!empty($tenant->gstin)) | GSTIN: {{ $tenant->gstin }}@endif
  </div>
  <div class="title">SALARY SLIP</div>
  <div style="font-size:11px;color:#666;">
    Period: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $salary->month)->format('F Y') }}</strong>
    &nbsp;|&nbsp;
    Status:
    @if($salary->payment_status === 'paid')
      <span class="paid-badge">PAID</span>
    @else
      <span class="pending-badge">PENDING</span>
    @endif
  </div>
</div>

{{-- Staff Info --}}
<div class="section">
  <div class="section-title">Employee Details</div>
  <div class="section-body">
    <table class="grid">
      <tr>
        <td><strong>Name:</strong> {{ $staff->name }}</td>
        <td><strong>Type:</strong> {{ ucfirst($staff->staff_type) }}</td>
      </tr>
      <tr>
        <td><strong>Phone:</strong> {{ $staff->phone }}</td>
        <td><strong>Date of Joining:</strong> {{ $staff->date_of_joining?->format('d-m-Y') ?? '—' }}</td>
      </tr>
      @if($staff->bank_name)
      <tr>
        <td><strong>Bank:</strong> {{ $staff->bank_name }}</td>
        <td><strong>Account:</strong> {{ $staff->bank_account }}</td>
      </tr>
      @endif
    </table>
  </div>
</div>

{{-- Attendance --}}
<div class="section">
  <div class="section-title">Attendance Summary</div>
  <div class="section-body">
    <table class="grid">
      <tr>
        <td><strong>Total Days:</strong> {{ $salary->total_days }}</td>
        <td><strong>Present Days:</strong> {{ $salary->present_days }}</td>
      </tr>
      <tr>
        <td><strong>Absent Days:</strong> {{ $salary->absent_days }}</td>
        <td><strong>Half Days:</strong> {{ $salary->half_days }}</td>
      </tr>
      <tr>
        <td><strong>Trip Days:</strong> {{ $salary->trip_days }}</td>
        <td></td>
      </tr>
    </table>
  </div>
</div>

{{-- Earnings --}}
<div class="section">
  <div class="section-title">Earnings</div>
  <div class="section-body">
    <table>
      <tr><td>Basic Salary</td><td class="right">₹ {{ number_format($salary->basic_salary, 2) }}</td></tr>
      @if($salary->hra > 0)
      <tr><td>HRA</td><td class="right">₹ {{ number_format($salary->hra, 2) }}</td></tr>
      @endif
      @if($salary->da_total > 0)
      <tr><td>DA (Trip Allowance)</td><td class="right">₹ {{ number_format($salary->da_total, 2) }}</td></tr>
      @endif
      @if($salary->bonus > 0)
      <tr><td>Bonus</td><td class="right">₹ {{ number_format($salary->bonus, 2) }}</td></tr>
      @endif
      @if($salary->other_allowance > 0)
      <tr><td>Other Allowance</td><td class="right">₹ {{ number_format($salary->other_allowance, 2) }}</td></tr>
      @endif
      <tr class="total-row">
        <td>Gross Salary</td>
        <td class="right">₹ {{ number_format($salary->gross_salary, 2) }}</td>
      </tr>
    </table>
  </div>
</div>

{{-- Deductions --}}
<div class="section">
  <div class="section-title">Deductions</div>
  <div class="section-body">
    <table>
      @if($salary->absent_deduction > 0)
      <tr><td>Absent / Half Day Deduction</td><td class="right">- ₹ {{ number_format($salary->absent_deduction, 2) }}</td></tr>
      @endif
      @if($salary->advance_deduction > 0)
      <tr><td>Advance Deduction</td><td class="right">- ₹ {{ number_format($salary->advance_deduction, 2) }}</td></tr>
      @endif
      @if($salary->other_deduction > 0)
      <tr><td>Other Deduction</td><td class="right">- ₹ {{ number_format($salary->other_deduction, 2) }}</td></tr>
      @endif
      <tr class="total-row">
        <td>Total Deduction</td>
        <td class="right">- ₹ {{ number_format($salary->total_deduction, 2) }}</td>
      </tr>
    </table>
  </div>
</div>

{{-- Net Salary --}}
<table>
  <tr class="net-row">
    <td>NET SALARY PAYABLE</td>
    <td class="right">₹ {{ number_format($salary->net_salary, 2) }}</td>
  </tr>
</table>

@if($salary->payment_status === 'paid')
<table class="grid" style="margin-top:8px;">
  <tr>
    <td><strong>Paid On:</strong> {{ $salary->paid_on?->format('d-m-Y') }}</td>
    <td><strong>Mode:</strong> {{ ucfirst($salary->payment_mode) }}</td>
  </tr>
  @if($salary->transaction_ref)
  <tr>
    <td colspan="2"><strong>Transaction Ref:</strong> {{ $salary->transaction_ref }}</td>
  </tr>
  @endif
</table>
@endif

<table style="margin-top:30px;">
  <tr>
    <td style="text-align:center;width:50%;">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:4px;font-size:11px;">Employee Signature</div>
    </td>
    <td style="text-align:center;width:50%;">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:4px;font-size:11px;">Authorized Signature</div>
    </td>
  </tr>
</table>

<div class="footer">
  This is a computer generated salary slip. | {{ $tenant->company_name ?? '' }} | Generated: {{ now()->format('d-m-Y H:i') }}
</div>

</body>
</html>
