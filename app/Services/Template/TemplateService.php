<?php

namespace App\Services\Template;

use App\Models\{Trip, Lead, Tenant, TemplateLog};

class TemplateService
{
    public function __construct(
        private InvoiceService    $invoice,
        private LetterheadService $letterhead,
        private QuotationService  $quotation,
        private EInvoiceService   $einvoice,
    ) {}

    public function tripInvoice(Trip $trip, Tenant $tenant, bool $withGst): array
    {
        return $withGst
            ? $this->invoice->generateGstInvoice($trip, $tenant)
            : $this->invoice->generateNonGstInvoice($trip, $tenant);
    }

    public function letterhead(Tenant $tenant, array $data): array
    {
        return $this->letterhead->generate($tenant, $data);
    }

    public function quotationFromLead(Lead $lead, Tenant $tenant): array
    {
        return $this->quotation->generateFromLead($lead, $tenant);
    }

    public function customQuotation(Tenant $tenant, array $data): array
    {
        return $this->quotation->generateCustom($tenant, $data);
    }

    public function uploadEInvoice(Trip $trip, Tenant $tenant): array
    {
        return $this->einvoice->uploadToGstPortal($trip, $tenant);
    }

    public function cancelEInvoice(TemplateLog $log, string $reason): array
    {
        return $this->einvoice->cancelEInvoice($log, $reason);
    }

    public function getEInvoicePayload(Trip $trip, Tenant $tenant): array
    {
        return $this->einvoice->generateEInvoicePayload($trip, $tenant);
    }

    public function getLogs(string $type = null, int $perPage = 20)
    {
        return TemplateLog::when($type, fn($q, $v) => $q->where('template_type', $v))
            ->latest()
            ->paginate($perPage);
    }
}
