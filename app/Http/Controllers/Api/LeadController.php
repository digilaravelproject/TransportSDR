<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\{
    StoreLeadRequest,
    UpdateLeadRequest,
    UpdateLeadStatusRequest,
    ConvertLeadRequest
};
use App\Http\Resources\{LeadResource, TripResource};
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $service) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/leads
    // Filters: status, source, from, to, search, followup_today
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $leads = Lead::with(['customer', 'assignedTo', 'creator'])
            ->when($request->status,  fn($q, $v) => $q->where('status', $v))
            ->when($request->source,  fn($q, $v) => $q->where('source', $v))
            ->when($request->from,    fn($q, $v) => $q->whereDate('trip_date', '>=', $v))
            ->when($request->to,      fn($q, $v) => $q->whereDate('trip_date', '<=', $v))
            ->when($request->assigned_to, fn($q, $v) => $q->where('assigned_to', $v))
            ->when($request->followup_today, function ($q) {
                return $q->whereDate('followup_date', today());
            })
            ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('lead_number',    'like', "%{$v}%")
                    ->orWhere('customer_name',    'like', "%{$v}%")
                    ->orWhere('customer_contact', 'like', "%{$v}%")
                    ->orWhere('trip_route',       'like', "%{$v}%");
            }))
            ->latest()
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => LeadResource::collection($leads),
            'meta'    => [
                'total'        => $leads->total(),
                'current_page' => $leads->currentPage(),
                'last_page'    => $leads->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/leads
    // ─────────────────────────────────────────────────
    public function store(StoreLeadRequest $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $lead = $this->service->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Lead {$lead->lead_number} created successfully.",
            'data'    => new LeadResource($lead),
        ], 201);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/leads/{id}
    // ─────────────────────────────────────────────────
    public function show(Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $lead->load(['customer', 'convertedTrip', 'assignedTo', 'creator']);

        return response()->json([
            'success' => true,
            'data'    => new LeadResource($lead),
        ]);
    }

    // ─────────────────────────────────────────────────
    // PUT /api/v1/leads/{id}
    // ─────────────────────────────────────────────────
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        if ($lead->isConverted()) {
            return response()->json([
                'success' => false,
                'message' => 'Converted lead cannot be edited.',
            ], 422);
        }

        $lead = $this->service->update($lead, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully.',
            'data'    => new LeadResource($lead),
        ]);
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/leads/{id}
    // ─────────────────────────────────────────────────
    public function destroy(Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin']);

        if ($lead->isConverted()) {
            return response()->json([
                'success' => false,
                'message' => 'Converted lead cannot be deleted.',
            ], 422);
        }

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/leads/{id}/status
    // Body: { status, followup_date, followup_notes, notes }
    // ─────────────────────────────────────────────────
    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $lead = $this->service->updateStatus($lead, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Lead status updated to: {$lead->status}.",
            'data'    => new LeadResource($lead),
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/leads/{id}/convert
    // Convert lead to trip
    // ─────────────────────────────────────────────────
    public function convert(ConvertLeadRequest $request, Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin']);

        $trip = $this->service->convertToTrip($lead, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Lead {$lead->lead_number} converted to Trip {$trip->trip_number} successfully.",
            'data'    => [
                'lead' => new LeadResource($lead->fresh()),
                'trip' => new TripResource($trip),
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/leads/stats
    // Lead summary for dashboard
    // ─────────────────────────────────────────────────
    public function stats()
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $stats = [
            'total'          => Lead::count(),
            'new'            => Lead::where('status', 'new')->count(),
            'contacted'      => Lead::where('status', 'contacted')->count(),
            'followup'       => Lead::where('status', 'followup')->count(),
            'quoted'         => Lead::where('status', 'quoted')->count(),
            'confirmed'      => Lead::where('status', 'confirmed')->count(),
            'converted'      => Lead::where('status', 'converted')->count(),
            'lost'           => Lead::where('status', 'lost')->count(),
            'cancelled'      => Lead::where('status', 'cancelled')->count(),
            'followup_today' => Lead::whereDate('followup_date', today())->count(),
            'by_source'      => Lead::selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }

    public function quotation(Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        try {
            $absolutePath = $this->service->generateQuotation($lead);

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation PDF could not be generated.',
                ], 500);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="quotation-' . $lead->lead_number . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/leads/{id}/bill
    // Generate & download bill PDF
    // ─────────────────────────────────────────────────
    public function bill(Lead $lead)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $absolutePath = $this->service->generateBill($lead);

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill PDF could not be generated.',
                ], 500);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="bill-' . $lead->lead_number . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
