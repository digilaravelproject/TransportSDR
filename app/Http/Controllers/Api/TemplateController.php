<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Trip, Lead, TemplateLog};
use App\Services\Template\TemplateService;
use Illuminate\Http\Request;
use Exception;

class TemplateController extends Controller
{
    public function __construct(private TemplateService $templateService) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/templates
    // List all generated template logs
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        $request->validate([
            'type' => 'nullable|in:invoice_gst,invoice_non_gst,letterhead,quotation,einvoice',
        ]);

        try {
            $logs = $this->templateService->getLogs(
                $request->type,
                $request->per_page ?? 20
            );

            return response()->json([
                'success' => true,
                'data'    => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching templates.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/templates/invoice/trip/{trip}
    // Generate invoice (with or without GST)
    // Body: { with_gst: true/false }
    // ─────────────────────────────────────────────────
    public function generateTripInvoice(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'with_gst' => 'required|boolean',
        ]);

        $tenant = auth()->user()->tenant;

        try {
            $result = $this->templateService->tripInvoice(
                $trip,
                $tenant,
                (bool) $request->with_gst
            );

            if (!file_exists($result['absolute_path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF generation failed.',
                ], 500);
            }

            return response()->file($result['absolute_path'], [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$result['file_name']}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during invoice generation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/templates/letterhead
    // Generate letterhead PDF
    // ─────────────────────────────────────────────────
    public function generateLetterhead(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'to'      => 'required|string|max:255',
            'content' => 'required|string',
            'date'    => 'nullable|date',
            'ref'     => 'nullable|string|max:100',
        ], [
            'subject.required' => 'Subject is required.',
            'to.required'      => 'Recipient is required.',
            'content.required' => 'Letter content is required.',
        ]);

        $tenant = auth()->user()->tenant;

        try {
            $result = $this->templateService->letterhead($tenant, $data);

            if (!file_exists($result['absolute_path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Letterhead generation failed.',
                ], 500);
            }

            return response()->file($result['absolute_path'], [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$result['file_name']}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the letterhead.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/templates/quotation/lead/{lead}
    // Generate quotation from lead
    // ─────────────────────────────────────────────────
    public function generateLeadQuotation(Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $tenant = auth()->user()->tenant;

        try {
            $result = $this->templateService->quotationFromLead($lead, $tenant);

            if (!file_exists($result['absolute_path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation generation failed.',
                ], 500);
            }

            return response()->file($result['absolute_path'], [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$result['file_name']}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the quotation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/templates/quotation/custom
    // Generate custom quotation
    // ─────────────────────────────────────────────────
    public function generateCustomQuotation(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_contact'   => 'required|string|max:15',
            'customer_email'     => 'nullable|email',
            'customer_address'   => 'nullable|string',
            'trip_route'         => 'required|string|max:255',
            'trip_date'          => 'required|date',
            'return_date'        => 'nullable|date',
            'duration_days'      => 'required|integer|min:1',
            'vehicle_type'       => 'required|string|max:100',
            'seating_capacity'   => 'required|integer|min:1',
            'number_of_vehicles' => 'required|integer|min:1',
            'pickup_address'     => 'required|string',
            'destinations'       => 'required|array|min:1',
            'destinations.*'     => 'required|string',
            'amount'             => 'required|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'is_gst'             => 'boolean',
            'gst_percent'        => 'nullable|numeric|min:0|max:28',
            'advance_required'   => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'valid_days'         => 'nullable|integer|min:1|max:30',
        ], [
            'customer_name.required'    => 'Customer name is required.',
            'trip_route.required'       => 'Trip route is required.',
            'vehicle_type.required'     => 'Vehicle type is required.',
            'amount.required'           => 'Amount is required.',
        ]);

        try {
            $tenant    = auth()->user()->tenant;
            $isGst     = $data['is_gst'] ?? false;
            $gstPct    = $data['gst_percent'] ?? 5;
            $taxAmt    = $isGst ? round($data['amount'] * $gstPct / 100, 2) : 0;
            $validDays = $data['valid_days'] ?? 7;

            $result = $this->templateService->customQuotation($tenant, [
                'customer' => [
                    'name'    => $data['customer_name'],
                    'contact' => $data['customer_contact'],
                    'email'   => $data['customer_email'] ?? null,
                    'address' => $data['customer_address'] ?? null,
                ],
                'trip' => [
                    'route'              => $data['trip_route'],
                    'date'               => \Carbon\Carbon::parse($data['trip_date'])->format('d-m-Y'),
                    'return_date'        => isset($data['return_date']) ? \Carbon\Carbon::parse($data['return_date'])->format('d-m-Y') : null,
                    'duration'           => $data['duration_days'],
                    'vehicle_type'       => $data['vehicle_type'],
                    'seating_capacity'   => $data['seating_capacity'],
                    'number_of_vehicles' => $data['number_of_vehicles'],
                    'pickup_address'     => $data['pickup_address'],
                    'destinations'       => $data['destinations'],
                ],
                'pricing' => [
                    'amount'           => $data['amount'],
                    'discount'         => $data['discount'] ?? 0,
                    'is_gst'           => $isGst,
                    'gst_percent'      => $gstPct,
                    'tax_amount'       => $taxAmt,
                    'total_with_tax'   => $data['amount'] + $taxAmt - ($data['discount'] ?? 0),
                    'advance_required' => $data['advance_required'] ?? 0,
                ],
                'date'       => now()->format('d-m-Y'),
                'valid_till' => now()->addDays($validDays)->format('d-m-Y'),
                'notes'      => $data['notes'] ?? null,
            ]);

            if (!file_exists($result['absolute_path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation generation failed.',
                ], 500);
            }

            return response()->file($result['absolute_path'], [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$result['file_name']}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the custom quotation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/templates/einvoice/payload/{trip}
    // Preview e-invoice JSON payload
    // ─────────────────────────────────────────────────
    public function eInvoicePayload(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $tenant  = auth()->user()->tenant;

        if (empty($tenant->gstin)) {
            return response()->json([
                'success' => false,
                'message' => 'GSTIN is required for e-invoice. Please update company settings.',
            ], 422);
        }

        try {
            return response()->json([
                'success' => true,
                'message' => 'E-invoice payload preview.',
                'data'    => $this->templateService->getEInvoicePayload($trip, $tenant),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating e-invoice payload.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/templates/einvoice/upload/{trip}
    // Upload e-invoice to GST portal
    // ─────────────────────────────────────────────────
    public function uploadEInvoice(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $tenant = auth()->user()->tenant;

        if (empty($tenant->gstin)) {
            return response()->json([
                'success' => false,
                'message' => 'GSTIN is required for e-invoice. Please update company settings.',
            ], 422);
        }

        if (!$trip->is_gst) {
            return response()->json([
                'success' => false,
                'message' => 'E-invoice can only be generated for GST trips.',
            ], 422);
        }

        try {
            $result = $this->templateService->uploadEInvoice($trip, $tenant);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            // Download the generated PDF
            if (isset($result['absolute_path']) && file_exists($result['absolute_path'])) {
                return response()->file($result['absolute_path'], [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => "inline; filename=\"einvoice-{$trip->trip_number}.pdf\"",
                ]);
            }

            return response()->json([
                'success'    => true,
                'message'    => $result['message'],
                'irn'        => $result['irn'],
                'ack_number' => $result['ack_number'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while uploading the e-invoice.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/templates/einvoice/{log}/cancel
    // Cancel e-invoice
    // ─────────────────────────────────────────────────
    public function cancelEInvoice(Request $request, TemplateLog $log)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $result = $this->templateService->cancelEInvoice($log, $data['reason']);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while canceling the e-invoice.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/templates/logs/{log}/download
    // Download any previously generated file
    // ─────────────────────────────────────────────────
    public function download(TemplateLog $log)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        if (!$log->file_path) {
            return response()->json([
                'success' => false,
                'message' => 'No file found for this log.',
            ], 404);
        }

        try {
            $absolutePath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, $log->file_path));

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server.',
                ], 404);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$log->file_name}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while downloading the file.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
