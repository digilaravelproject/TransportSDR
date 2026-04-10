<?php

namespace App\Services;

use App\Models\{Corporate, CorporateDuty, CorporatePayment, CorporateFine};
use Illuminate\Support\Facades\{DB, File};
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CorporateService
{
    // ── Generate Monthly Invoice ───────────────────────
    public function generateMonthlyInvoice(Corporate $corporate, string $from, string $to): CorporatePayment
    {
        return DB::transaction(function () use ($corporate, $from, $to) {

            $duties = CorporateDuty::where('corporate_id', $corporate->id)
                ->whereBetween('duty_date', [$from, $to])
                ->where('duty_status', 'completed')
                ->get();

            $totalDuties    = $duties->count();
            $holidayDuties  = $duties->where('is_holiday', true)->count();
            $extraDuties    = $duties->where('is_extra_duty', true)->count();
            $totalKm        = $duties->sum('total_km');
            $extraKm        = $duties->sum('extra_km');

            // Amount breakdown
            $baseAmount      = $corporate->contract_type === 'monthly'
                ? $corporate->monthly_package
                : $duties->sum('base_amount');

            $extraKmAmount   = $duties->sum('extra_km_amount');
            $extraHourAmount = $duties->sum('extra_hour_amount');
            $holidayAmount   = $duties->sum('holiday_amount');
            $extraDutyAmount = $duties->sum('extra_duty_amount');

            // Fine deduction
            $fines = CorporateFine::where('corporate_id', $corporate->id)
                ->where('status', 'pending')
                ->whereBetween('fine_date', [$from, $to])
                ->sum('amount');

            $subtotal = $baseAmount + $extraKmAmount + $extraHourAmount
                + $holidayAmount + $extraDutyAmount - $fines;

            $period = Carbon::parse($from)->format('Y-m');

            $payment = CorporatePayment::create([
                'corporate_id'     => $corporate->id,
                'billing_period'   => $period,
                'billing_from'     => $from,
                'billing_to'       => $to,
                'total_duties'     => $totalDuties,
                'holiday_duties'   => $holidayDuties,
                'extra_duties'     => $extraDuties,
                'total_km'         => $totalKm,
                'extra_km'         => $extraKm,
                'base_amount'      => $baseAmount,
                'extra_km_amount'  => $extraKmAmount,
                'extra_hour_amount' => $extraHourAmount,
                'holiday_amount'   => $holidayAmount,
                'extra_duty_amount' => $extraDutyAmount,
                'fine_deduction'   => $fines,
                'subtotal'         => $subtotal,
                'is_gst'           => $corporate->is_gst,
                'gst_percent'      => $corporate->gst_percent,
                'paid_amount'      => 0,
            ]);

            // Mark fines as deducted
            CorporateFine::where('corporate_id', $corporate->id)
                ->where('status', 'pending')
                ->whereBetween('fine_date', [$from, $to])
                ->update(['status' => 'deducted', 'payment_id' => $payment->id]);

            return $payment;
        });
    }

    // ── Generate Invoice PDF ───────────────────────────
    public function generateInvoicePdf(CorporatePayment $payment): string
    {
        $payment->loadMissing('corporate');
        $corporate = $payment->corporate;
        $tenant    = auth()->user()->tenant;

        $duties = CorporateDuty::where('corporate_id', $corporate->id)
            ->whereBetween('duty_date', [$payment->billing_from, $payment->billing_to])
            ->where('duty_status', 'completed')
            ->with(['driver', 'vehicle'])
            ->orderBy('duty_date')
            ->get();

        $absoluteDir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $corporate->tenant_id . DIRECTORY_SEPARATOR .
                'corporate-invoices'
        );

        $fileName     = "invoice-{$payment->invoice_number}.pdf";
        $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0775, true);
        }

        Pdf::loadView('pdf.corporate-invoice', [
            'payment'   => $payment,
            'corporate' => $corporate,
            'tenant'    => $tenant,
            'duties'    => $duties,
        ])->setPaper('a4')->save($absoluteFile);

        $storagePath = "tenants/{$corporate->tenant_id}/corporate-invoices/{$fileName}";
        $payment->update(['invoice_path' => $storagePath]);

        return $absoluteFile;
    }

    // ── Calculate duty billing ─────────────────────────
    public function calculateDutyBilling(CorporateDuty $duty): array
    {
        $corporate = $duty->corporate;

        $extraKm        = max(0, ($duty->total_km ?? 0) - $corporate->included_km);
        $extraKmAmount  = round($extraKm * $corporate->per_km_rate, 2);
        $extraHrAmount  = round(($duty->extra_hours ?? 0) * $corporate->extra_hour_rate, 2);
        $holidayAmount  = $duty->is_holiday    ? $corporate->holiday_rate    : 0;
        $extraDutyAmt   = $duty->is_extra_duty ? $corporate->extra_duty_rate : 0;

        $baseAmount = match ($corporate->contract_type) {
            'monthly' => 0,
            'daily'   => $corporate->per_day_rate,
            default   => 0,
        };

        $total = $baseAmount + $extraKmAmount + $extraHrAmount
            + $holidayAmount + $extraDutyAmt - ($duty->fine_amount ?? 0);

        return [
            'base_amount'       => $baseAmount,
            'extra_km'          => $extraKm,
            'extra_km_amount'   => $extraKmAmount,
            'extra_hour_amount' => $extraHrAmount,
            'holiday_amount'    => $holidayAmount,
            'extra_duty_amount' => $extraDutyAmt,
            'total_amount'      => $total,
        ];
    }
}
