<?php

namespace App\Services\Template;

use App\Models\{Lead, Trip, Tenant, TemplateLog};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class QuotationService
{
    // ── From Lead ──────────────────────────────────────
    public function generateFromLead(Lead $lead, Tenant $tenant): array
    {
        $lead->loadMissing(['customer']);

        $result = $this->generatePdf(
            $tenant->id,
            'pdf.templates.quotation',
            [
                'tenant'     => $tenant,
                'type'       => 'lead',
                'number'     => $lead->lead_number,
                'date'       => now()->format('d-m-Y'),
                'valid_till' => now()->addDays(7)->format('d-m-Y'),
                'customer'   => [
                    'name'    => $lead->customer_name,
                    'contact' => $lead->customer_contact,
                    'email'   => $lead->customer_email,
                    'address' => $lead->customer?->address,
                    'gstin'   => $lead->customer?->gstin,
                ],
                'trip'       => [
                    'route'              => $lead->trip_route,
                    'date'               => $lead->trip_date->format('d-m-Y'),
                    'return_date'        => $lead->return_date?->format('d-m-Y'),
                    'duration'           => $lead->duration_days,
                    'vehicle_type'       => $lead->vehicle_type,
                    'seating_capacity'   => $lead->seating_capacity,
                    'number_of_vehicles' => $lead->number_of_vehicles,
                    'pickup_address'     => $lead->pickup_address,
                    'destinations'       => $lead->destination_points,
                ],
                'pricing'    => [
                    'amount'          => $lead->quoted_amount,
                    'discount'        => $lead->discount,
                    'is_gst'          => $lead->is_gst,
                    'gst_percent'     => $lead->gst_percent,
                    'tax_amount'      => $lead->tax_amount,
                    'total_with_tax'  => $lead->total_with_tax,
                    'advance_required' => $lead->advance_amount,
                ],
                'notes'      => $lead->notes,
            ],
            "quotation-{$lead->lead_number}.pdf"
        );

        TemplateLog::create([
            'template_type'    => 'quotation',
            'reference_type'   => 'Lead',
            'reference_id'     => $lead->id,
            'reference_number' => $lead->lead_number,
            'file_path'        => $result['storage'],
            'file_name'        => "quotation-{$lead->lead_number}.pdf",
        ]);

        $lead->update(['quotation_path' => $result['storage']]);

        return [
            'absolute_path' => $result['absolute'],
            'file_name'     => "quotation-{$lead->lead_number}.pdf",
        ];
    }

    // ── From custom data ───────────────────────────────
    public function generateCustom(Tenant $tenant, array $data): array
    {
        $result = $this->generatePdf(
            $tenant->id,
            'pdf.templates.quotation',
            array_merge($data, [
                'tenant' => $tenant,
                'type'   => 'custom',
                'number' => 'QUO-' . now()->format('YmdHis'),
                'date'   => now()->format('d-m-Y'),
            ]),
            'quotation-custom-' . now()->format('YmdHis') . '.pdf'
        );

        TemplateLog::create([
            'template_type' => 'quotation',
            'file_path'     => $result['storage'],
            'file_name'     => 'quotation-custom-' . now()->format('YmdHis') . '.pdf',
        ]);

        return [
            'absolute_path' => $result['absolute'],
            'file_name'     => 'quotation-custom.pdf',
        ];
    }

    private function generatePdf(int $tenantId, string $view, array $data, string $fileName): array
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR . 'quotations'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $absoluteFile = $dir . DIRECTORY_SEPARATOR . $fileName;
        $storagePath  = "tenants/{$tenantId}/quotations/{$fileName}";

        Pdf::loadView($view, $data)->setPaper('a4')->save($absoluteFile);

        return ['absolute' => $absoluteFile, 'storage' => $storagePath];
    }
}
