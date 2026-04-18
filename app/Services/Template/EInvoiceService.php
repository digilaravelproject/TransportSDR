<?php

namespace App\Services\Template;

use App\Models\{Trip, Tenant, TemplateLog};
use Illuminate\Support\Facades\{Http, File, Log};
use Barryvdh\DomPDF\Facade\Pdf;

class EInvoiceService
{
    // ── Generate E-Invoice JSON (GSTN format) ──────────
    public function generateEInvoicePayload(Trip $trip, Tenant $tenant): array
    {
        $trip->loadMissing(['customer', 'vehicle']);

        $gst     = app(\App\Services\Template\InvoiceService::class)->calculateGst($trip);
        $invoiceNo = $trip->trip_number;

        return [
            'Version'     => '1.1',
            'TranDtls'    => [
                'TaxSch'    => 'GST',
                'SupTyp'    => 'B2B',
                'RegRev'    => 'N',
                'EcmGstin'  => null,
            ],
            'DocDtls'     => [
                'Typ'   => 'INV',
                'No'    => $invoiceNo,
                'Dt'    => $trip->trip_date->format('d/m/Y'),
            ],
            'SellerDtls'  => [
                'Gstin'    => $tenant->gstin ?? '',
                'LglNm'    => $tenant->company_name,
                'TrdNm'    => $tenant->company_name,
                'Addr1'    => $tenant->address ?? '',
                'Loc'      => 'Lucknow',
                'Pin'      => 226001,
                'Stcd'     => '09',
                'Ph'       => $tenant->phone,
                'Em'       => $tenant->email,
            ],
            'BuyerDtls'   => [
                'Gstin'    => $trip->customer?->gstin ?? 'URP',
                'LglNm'    => $trip->customer_name,
                'TrdNm'    => $trip->customer_name,
                'Pos'      => '09',
                'Addr1'    => $trip->pickup_address ?? '',
                'Loc'      => 'Lucknow',
                'Pin'      => 226001,
                'Stcd'     => '09',
                'Ph'       => $trip->customer_contact,
                'Em'       => $trip->customer?->email ?? '',
            ],
            'ItemList'    => [
                [
                    'SlNo'       => '1',
                    'PrdDesc'    => "Transport Service: {$trip->trip_route}",
                    'IsServc'    => 'Y',
                    'HsnCd'      => '996421',
                    'Qty'        => 1,
                    'Unit'       => 'OTH',
                    'UnitPrice'  => round($trip->total_amount - $trip->discount, 2),
                    'TotAmt'     => round($trip->total_amount - $trip->discount, 2),
                    'Discount'   => round($trip->discount, 2),
                    'AssAmt'     => round($trip->total_amount - $trip->discount, 2),
                    'GstRt'      => $trip->gst_percent ?? 5,
                    'CgstAmt'    => $gst['cgst'],
                    'SgstAmt'    => $gst['sgst'],
                    'IgstAmt'    => $gst['igst'],
                    'TotItemVal' => $gst['grand_total'],
                ],
            ],
            'ValDtls'     => [
                'AssVal'     => round($trip->total_amount - $trip->discount, 2),
                'CgstVal'    => $gst['cgst'],
                'SgstVal'    => $gst['sgst'],
                'IgstVal'    => $gst['igst'],
                'TotInvVal'  => $gst['grand_total'],
                'RndOffAmt'  => 0,
            ],
            'PayDtls'     => [
                'Nm'     => $trip->customer_name,
                'Mode'   => 'Cash',
                'PayTerm' => '0',
                'PaidAmt' => round($trip->advance_amount, 2),
                'PaymtDue' => round($trip->balance_amount, 2),
            ],
        ];
    }

    // ── Upload to GST portal ───────────────────────────
    public function uploadToGstPortal(Trip $trip, Tenant $tenant): array
    {
        // Check existing log
        $existing = TemplateLog::where('reference_type', 'Trip')
            ->where('reference_id', $trip->id)
            ->where('template_type', 'einvoice')
            ->where('einvoice_status', 'uploaded')
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'E-invoice already uploaded for this trip.',
                'irn'     => $existing->irn,
                'log'     => $existing,
            ];
        }

        $payload = $this->generateEInvoicePayload($trip, $tenant);

        // Create log entry
        $log = TemplateLog::create([
            'template_type'    => 'einvoice',
            'reference_type'   => 'Trip',
            'reference_id'     => $trip->id,
            'reference_number' => $trip->trip_number,
            'einvoice_status'  => 'not_uploaded',
        ]);

        try {
            // NOTE: Replace with actual GST portal API
            // Sandbox: https://einv-apisandbox.nic.in
            // Production: https://api.mastergst.com or NIC direct
            $response = $this->callGstApi($payload, $tenant);

            if ($response['success']) {
                // Generate IRN QR code image
                $qrPath = $this->generateQrCode(
                    $response['irn'],
                    $tenant->id,
                    $trip->trip_number
                );

                // Generate e-invoice PDF with IRN + QR
                $pdfResult = $this->generateEInvoicePdf($trip, $tenant, $response, $qrPath);

                $log->update([
                    'irn'              => $response['irn'],
                    'ack_number'       => $response['ack_number'],
                    'ack_date'         => $response['ack_date'],
                    'qr_code_path'     => $qrPath,
                    'file_path'        => $pdfResult['storage'],
                    'file_name'        => $pdfResult['file_name'],
                    'einvoice_status'  => 'uploaded',
                    'einvoice_response' => json_encode($response),
                ]);

                return [
                    'success'      => true,
                    'message'      => 'E-invoice uploaded to GST portal successfully.',
                    'irn'          => $response['irn'],
                    'ack_number'   => $response['ack_number'],
                    'ack_date'     => $response['ack_date'],
                    'absolute_path' => $pdfResult['absolute'],
                    'log'          => $log->fresh(),
                ];
            }

            $log->update([
                'einvoice_status'  => 'failed',
                'einvoice_response' => json_encode($response),
            ]);

            return [
                'success' => false,
                'message' => 'GST portal upload failed: ' . ($response['message'] ?? 'Unknown error'),
                'log'     => $log->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('E-Invoice upload failed', [
                'trip_id' => $trip->id,
                'error'   => $e->getMessage(),
            ]);

            $log->update([
                'einvoice_status'  => 'failed',
                'einvoice_response' => json_encode(['error' => $e->getMessage()]),
            ]);

            return [
                'success' => false,
                'message' => 'E-invoice upload failed: ' . $e->getMessage(),
                'log'     => $log->fresh(),
            ];
        }
    }

    // ── Cancel E-Invoice ───────────────────────────────
    public function cancelEInvoice(TemplateLog $log, string $reason): array
    {
        if ($log->einvoice_status !== 'uploaded') {
            return ['success' => false, 'message' => 'Only uploaded e-invoices can be cancelled.'];
        }

        try {
            // Call GST cancel API
            // $response = Http::post('https://gst-api.com/cancel', [...]);

            $log->update(['einvoice_status' => 'cancelled']);

            return [
                'success' => true,
                'message' => 'E-invoice cancelled successfully.',
                'log'     => $log->fresh(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Private: Call GST API ──────────────────────────
    private function callGstApi(array $payload, Tenant $tenant): array
    {
        // ─── SANDBOX / MOCK RESPONSE ─────────────────
        // Replace this with actual GST API call:
        //
        // Option 1: NIC Direct API
        // $response = Http::withHeaders([
        //     'client_id'     => config('services.gst.client_id'),
        //     'client_secret' => config('services.gst.client_secret'),
        //     'gstin'         => $tenant->gstin,
        //     'user_name'     => config('services.gst.username'),
        // ])->post('https://einv-apisandbox.nic.in/eicore/v1.03/Invoice', $payload);
        //
        // Option 2: MasterGST / ClearTax / Razorpay
        // $response = Http::withToken(config('services.mastergst.token'))
        //     ->post('https://api.mastergst.com/einvoice/type/GENERATE/...', $payload);

        // MOCK response for development:
        return [
            'success'    => true,
            'irn'        => 'a5c12d8f9e3b7a4c1d2e6f0a8b3c5d7e9f1a2b4c6d8e0f2a4b6c8d0e2f4a6b8',
            'ack_number' => '112345678901234',
            'ack_date'   => now()->format('Y-m-d H:i:s'),
            'signed_qr'  => 'MOCK_QR_DATA_' . uniqid(),
        ];
    }

    // ── Private: Generate QR code ──────────────────────
    private function generateQrCode(string $irn, int $tenantId, string $tripNumber): string
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR . 'einvoice-qr'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName = "qr-{$tripNumber}.png";
        $path     = $dir . DIRECTORY_SEPARATOR . $fileName;

        // Using SimpleSoftwareIO/simple-qrcode if installed
        // QrCode::format('png')->size(200)->generate($irn, $path);

        // Fallback: Google Charts QR API
        $qrUrl    = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($irn);
        $qrImage  = @file_get_contents($qrUrl);

        if ($qrImage) {
            file_put_contents($path, $qrImage);
        }

        return "tenants/{$tenantId}/einvoice-qr/{$fileName}";
    }

    // ── Private: Generate E-Invoice PDF ───────────────
    private function generateEInvoicePdf(Trip $trip, Tenant $tenant, array $response, string $qrPath): array
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenant->id . DIRECTORY_SEPARATOR . 'einvoices'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName     = "einvoice-{$trip->trip_number}.pdf";
        $absoluteFile = $dir . DIRECTORY_SEPARATOR . $fileName;
        $storagePath  = "tenants/{$tenant->id}/einvoices/{$fileName}";
        $gst          = app(\App\Services\Template\InvoiceService::class)->calculateGst($trip);

        Pdf::loadView('pdf.templates.invoice-gst', [
            'trip'        => $trip,
            'tenant'      => $tenant,
            'gst'         => $gst,
            'einvoice'    => [
                'irn'        => $response['irn'],
                'ack_number' => $response['ack_number'],
                'ack_date'   => $response['ack_date'],
                'qr_url'     => $qrPath ? asset("storage/{$qrPath}") : null,
            ],
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        return [
            'absolute'  => $absoluteFile,
            'storage'   => $storagePath,
            'file_name' => $fileName,
        ];
    }
}
