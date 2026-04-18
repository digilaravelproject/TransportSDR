<?php

namespace App\Services\Template;

use App\Models\{Trip, TemplateLog, Tenant};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class InvoiceService
{
    // ── Invoice with GST ───────────────────────────────
    public function generateGstInvoice(Trip $trip, Tenant $tenant): array
    {
        $trip->loadMissing(['vehicle', 'customer', 'driver', 'payments']);

        $absoluteFile = $this->buildPath(
            $tenant->id,
            'invoices/gst',
            "invoice-gst-{$trip->trip_number}.pdf"
        );

        Pdf::loadView('pdf.templates.invoice-gst', [
            'trip'   => $trip,
            'tenant' => $tenant,
            'gst'    => $this->calculateGst($trip),
        ])
            ->setPaper('a4')
            ->save($absoluteFile['absolute']);

        $log = TemplateLog::create([
            'template_type'    => 'invoice_gst',
            'reference_type'   => 'Trip',
            'reference_id'     => $trip->id,
            'reference_number' => $trip->trip_number,
            'file_path'        => $absoluteFile['storage'],
            'file_name'        => "invoice-gst-{$trip->trip_number}.pdf",
        ]);

        $trip->update(['invoice_path' => $absoluteFile['storage']]);

        return [
            'absolute_path' => $absoluteFile['absolute'],
            'file_name'     => "invoice-gst-{$trip->trip_number}.pdf",
            'log'           => $log,
        ];
    }

    // ── Invoice without GST ────────────────────────────
    public function generateNonGstInvoice(Trip $trip, Tenant $tenant): array
    {
        $trip->loadMissing(['vehicle', 'customer', 'driver', 'payments']);

        $absoluteFile = $this->buildPath(
            $tenant->id,
            'invoices/non-gst',
            "invoice-{$trip->trip_number}.pdf"
        );

        Pdf::loadView('pdf.templates.invoice-non-gst', [
            'trip'   => $trip,
            'tenant' => $tenant,
        ])
            ->setPaper('a4')
            ->save($absoluteFile['absolute']);

        $log = TemplateLog::create([
            'template_type'    => 'invoice_non_gst',
            'reference_type'   => 'Trip',
            'reference_id'     => $trip->id,
            'reference_number' => $trip->trip_number,
            'file_path'        => $absoluteFile['storage'],
            'file_name'        => "invoice-{$trip->trip_number}.pdf",
        ]);

        $trip->update(['invoice_path' => $absoluteFile['storage']]);

        return [
            'absolute_path' => $absoluteFile['absolute'],
            'file_name'     => "invoice-{$trip->trip_number}.pdf",
            'log'           => $log,
        ];
    }

    // ── GST breakdown ──────────────────────────────────
    public function calculateGst(Trip $trip): array
    {
        $taxableAmount = $trip->total_amount - $trip->discount;
        $gstPercent    = $trip->gst_percent ?? 5;
        $halfGst       = $gstPercent / 2;

        // Same state = CGST + SGST, different state = IGST
        $cgst = round($taxableAmount * $halfGst / 100, 2);
        $sgst = round($taxableAmount * $halfGst / 100, 2);
        $igst = 0;

        return [
            'taxable_amount' => round($taxableAmount, 2),
            'gst_percent'    => $gstPercent,
            'cgst_percent'   => $halfGst,
            'sgst_percent'   => $halfGst,
            'cgst'           => $cgst,
            'sgst'           => $sgst,
            'igst'           => $igst,
            'total_tax'      => $cgst + $sgst + $igst,
            'grand_total'    => round($taxableAmount + $cgst + $sgst, 2),
        ];
    }

    // ── Path builder (Windows + Linux safe) ───────────
    private function buildPath(int $tenantId, string $folder, string $fileName): array
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, $folder)
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        return [
            'absolute' => $dir . DIRECTORY_SEPARATOR . $fileName,
            'storage'  => "tenants/{$tenantId}/{$folder}/{$fileName}",
        ];
    }
}
