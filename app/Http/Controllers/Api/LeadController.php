<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Lead, LeadNote, LeadFollowUp, LeadExpense, LeadDutySheet, Vehicle, Staff};
use App\Services\Template\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\Notification\NotificationService;

class LeadController extends Controller
{
    public function __construct(private TemplateService $templateService, private NotificationService $notificationService) {}
    // GET /api/v1/leads
    public function index(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('trip_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('trip_date', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_contact', 'like', "%{$s}%")
                    ->orWhere('trip_route', 'like', "%{$s}%")
                    ->orWhere('lead_number', 'like', "%{$s}%");
            });
        }

        $perPage = (int) ($request->per_page ?? 20);
        $leads = $query->with(['notes.author', 'followups.author', 'vehicle', 'driver', 'expenses.creator', 'dutySheets.uploader'])->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $leads->items(),
            'meta'    => [
                'total' => $leads->total(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
            ],
        ]);
    }

    // POST /api/v1/leads
    public function store(Request $request)
    {
        $rules = [
            'trip_route' => 'required|string',
            'trip_date' => 'required|date',
            'duration_days' => 'required|integer|min:1',
            'vehicle_type' => 'required|string',
            'seating_capacity' => 'required|integer|min:1',
            'pickup_address' => 'required|string',
            'points' => 'required|array|min:1',
            'points.*.type' => 'required|string',
            'points.*.name' => 'required|string',
            'points.*.lat' => 'required|numeric',
            'points.*.lng' => 'required|numeric',
            'points.*.order' => 'required|integer',
            'customer_name' => 'required|string',
            'customer_contact' => 'required|string',
            'total_amount' => 'required|numeric',
            'advance_amount' => 'sometimes|numeric',
            'pending_amount' => 'sometimes|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Ensure numeric defaults
        $data['advance_amount'] = $data['advance_amount'] ?? 0;
        $data['pending_amount'] = $data['pending_amount'] ?? max(0, ($data['total_amount'] - $data['advance_amount']));
        $data['tenant_id'] = auth()->user()->tenant_id ?? null;

        $lead = Lead::create($data);

        // generate quotation PDF and store path on lead
        try {
            $tenant = auth()->user()->tenant;
            $this->templateService->quotationFromLead($lead, $tenant);
        } catch (\Throwable $e) {
            // ignore PDF generation failure for now
        }
        // create notification
        try {
            $this->notificationService->create('New Lead: ' . $lead->customer_name, "New lead created: {$lead->lead_number}", ['lead_id' => $lead->id], 'lead', 'high');
        } catch (\Throwable $e) {
            // ignore notification failure
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully.',
            'data' => $lead,
        ], 201);
    }

    // GET /api/v1/leads/{lead}
    public function show(Lead $lead)
    {
        return response()->json(['success' => true, 'data' => $lead]);
    }

    // PUT /api/v1/leads/{lead}
    public function update(Request $request, Lead $lead)
    {
        $rules = [
            'trip_route' => 'sometimes|string',
            'trip_date' => 'sometimes|date',
            'duration_days' => 'sometimes|integer|min:1',
            'vehicle_type' => 'sometimes|string',
            'seating_capacity' => 'sometimes|integer|min:1',
            'pickup_address' => 'sometimes|string',
            'points' => 'sometimes|array|min:1',
            'customer_name' => 'sometimes|string',
            'customer_contact' => 'sometimes|string',
            'total_amount' => 'sometimes|numeric',
            'advance_amount' => 'sometimes|numeric',
            'pending_amount' => 'sometimes|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lead->update($validator->validated());

        // regenerate quotation PDF when lead changes
        try {
            $tenant = auth()->user()->tenant;
            $this->templateService->quotationFromLead($lead, $tenant);
        } catch (\Throwable $e) {
            // ignore failures
        }

        try {
            $this->notificationService->create('Lead Updated: ' . $lead->customer_name, "Lead {$lead->lead_number} updated", ['lead_id' => $lead->id], 'lead', 'medium');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Lead updated.', 'data' => $lead]);
    }

    // DELETE /api/v1/leads/{lead}
    public function destroy(Lead $lead)
    {
        $lead->delete();
        try {
            $this->notificationService->create('Lead Deleted: ' . $lead->customer_name, "Lead {$lead->lead_number} deleted", ['lead_id' => $lead->id], 'lead', 'medium');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Lead deleted.']);
    }

    // PATCH /api/v1/leads/{lead}/status
    public function updateStatus(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,contacted,followup,quoted,confirmed,cancelled,converted'
        ]);

        $lead->status = $data['status'];

        // option to set followup date from request
        if ($request->filled('followup_at')) {
            $lead->followup_date = $request->followup_at;
        }

        $lead->save();
        try {
            $this->notificationService->create('Lead Status: ' . $lead->customer_name, "Status changed to {$lead->status} for {$lead->lead_number}", ['lead_id' => $lead->id, 'status' => $lead->status], 'lead_status', 'high');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Status updated.', 'data' => $lead]);
    }

    // GET /api/v1/leads/{lead}/notes
    public function notes(Lead $lead)
    {
        $notes = $lead->notes()->with('author')->get();
        return response()->json(['success' => true, 'data' => $notes]);
    }

    // POST /api/v1/leads/{lead}/notes
    public function addNote(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'note' => 'required|string',
        ]);

        $note = LeadNote::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'lead_id'   => $lead->id,
            'created_by'=> auth()->id(),
            'note'      => $data['note'],
        ]);
        try {
            $this->notificationService->create('Lead Note Added', "Note added to {$lead->lead_number}", ['lead_id' => $lead->id, 'note_id' => $note->id], 'lead_note', 'low');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Note added.', 'data' => $note], 201);
    }

    // GET /api/v1/leads/{lead}/followups
    public function followups(Lead $lead)
    {
        $items = $lead->followups()->with('author')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/v1/leads/{lead}/followups
    public function addFollowup(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'reminder_at' => 'required',
            'note' => 'nullable|string',
        ]);

        try {
            // Accept flexible datetime formats and normalize
            $reminderAt = \Carbon\Carbon::parse($validated['reminder_at']);

            $fu = LeadFollowUp::create([
                'tenant_id'  => auth()->user()->tenant_id ?? null,
                'lead_id'    => $lead->id,
                'created_by' => auth()->id(),
                'reminder_at'=> $reminderAt->toDateTimeString(),
                'note'       => $validated['note'] ?? null,
            ]);

            // also update lead followup_date for dashboard queries if column exists
            if (\Schema::hasColumn('leads', 'followup_date')) {
                $lead->followup_date = $reminderAt->toDateString();
                $lead->save();
            }

            return response()->json(['success' => true, 'message' => 'Follow up scheduled.', 'data' => $fu], 201);
        } catch (\Exception $e) {
            try {
                $this->notificationService->create('Lead Followup Failed', "Followup scheduling failed for lead {$lead->lead_number}", ['error' => $e->getMessage()], 'error', 'high');
            } catch (\Throwable $t) {}

            return response()->json(['success' => false, 'message' => 'Failed to schedule followup.', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/v1/leads/{lead}/bill
    public function bill(Lead $lead)
    {
        try {
            // If quotation path already exists, return its public URL
            if (!empty($lead->quotation_path) && \Storage::disk('public')->exists($lead->quotation_path)) {
                $url = asset('storage/' . $lead->quotation_path);
                return response()->json(['success' => true, 'data' => ['url' => $url]]);
            }

            // otherwise generate and save
            $tenant = auth()->user()->tenant;
            $result = $this->templateService->quotationFromLead($lead, $tenant);

            if (empty($lead->quotation_path) && isset($result['absolute_path'])) {
                // attempt to compute storage path from result if updated
                // QuotationService already updates lead->quotation_path when generating
            }

            if (!empty($lead->quotation_path) && \Storage::disk('public')->exists($lead->quotation_path)) {
                $url = asset('storage/' . $lead->quotation_path);
                return response()->json(['success' => true, 'data' => ['url' => $url]]);
            }

            // fallback: if service returned absolute path, compute url
            if (!empty($result['absolute_path']) && file_exists($result['absolute_path'])) {
                // convert absolute to storage relative if possible
                $storageRelative = str_replace(storage_path('app/public') . DIRECTORY_SEPARATOR, '', $result['absolute_path']);
                $url = asset('storage/' . str_replace(DIRECTORY_SEPARATOR, '/', $storageRelative));
                return response()->json(['success' => true, 'data' => ['url' => $url]]);
            }

            return response()->json(['success' => false, 'message' => 'PDF not available.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/v1/leads/vehicles/list
    public function vehicleList()
    {
        $vehicles = Vehicle::select('id','registration_number','type','seating_capacity','is_available')->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'registration_number' => $v->registration_number,
                'type' => $v->type,
                'seating_capacity' => $v->seating_capacity,
                'status' => $v->is_available ? 'available' : 'on_trip'
            ]);

        return response()->json(['success' => true, 'data' => $vehicles]);
    }

    // POST /api/v1/leads/{lead}/assign-vehicle
    public function assignVehicle(Request $request, Lead $lead)
    {
        $data = $request->validate(['vehicle_id' => 'required|integer|exists:vehicles,id']);
        $vehicleId = $data['vehicle_id'];

        // Release previous vehicle
        if ($lead->vehicle_id) {
            Vehicle::find($lead->vehicle_id)?->update(['is_available' => true]);
        }

        // Assign new vehicle
        Vehicle::find($vehicleId)?->update(['is_available' => false]);
        $lead->vehicle_id = $vehicleId;
        $lead->save();
        try {
            $this->notificationService->create('Vehicle Assigned', "Vehicle assigned to {$lead->lead_number}", ['lead_id' => $lead->id, 'vehicle_id' => $vehicleId], 'assign', 'medium');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Vehicle assigned.', 'data' => $lead->load('vehicle')]);
    }

    // GET /api/v1/leads/drivers/list
    public function driverList()
    {
        $drivers = Staff::drivers()->select('id','name','phone','is_available')->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'phone' => $d->phone,
                'status' => $d->is_available ? 'available' : 'on_trip'
            ]);

        return response()->json(['success' => true, 'data' => $drivers]);
    }

    // POST /api/v1/leads/{lead}/assign-driver
    public function assignDriver(Request $request, Lead $lead)
    {
        $data = $request->validate(['driver_id' => 'required|integer|exists:staff,id']);
        $driverId = $data['driver_id'];

        if ($lead->driver_id) {
            Staff::find($lead->driver_id)?->update(['is_available' => true]);
        }

        Staff::find($driverId)?->update(['is_available' => false]);
        $lead->driver_id = $driverId;
        $lead->save();
        try {
            $this->notificationService->create('Driver Assigned', "Driver assigned to {$lead->lead_number}", ['lead_id' => $lead->id, 'driver_id' => $driverId], 'assign', 'medium');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Driver assigned.', 'data' => $lead->load('driver')]);
    }

    // GET /api/v1/leads/{lead}/expenses
    public function expenses(Lead $lead)
    {
        $items = $lead->expenses()->with('creator')->get();
        $total = $lead->expenses()->sum('amount');
        return response()->json(['success' => true, 'data' => $items, 'total' => (float) $total]);
    }

    // POST /api/v1/leads/{lead}/expenses
    // Accepts multipart/form-data for optional receipt file
    public function addExpense(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'category' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'entry_date' => 'nullable|date',
            'receipt' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $path = $file->storePubliclyAs(
                "tenants/" . (auth()->user()->tenant_id ?? '0') . "/leads/{$lead->id}/receipts",
                time() . '_' . $file->getClientOriginalName(),
                'public'
            );
        }

        $expense = LeadExpense::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'lead_id' => $lead->id,
            'category' => $data['category'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'entry_date' => $data['entry_date'] ?? now()->toDateString(),
            'receipt_path' => $path,
            'created_by' => auth()->id(),
        ]);
        try {
            $this->notificationService->create('Expense Added', "Expense of {$expense->amount} added to {$lead->lead_number}", ['lead_id' => $lead->id, 'expense_id' => $expense->id], 'expense', 'low');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Expense added.', 'data' => $expense], 201);
    }

    // GET /api/v1/leads/{lead}/duty-sheets
    public function dutySheets(Lead $lead)
    {
        $items = $lead->dutySheets()->with('uploader')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/v1/leads/{lead}/duty-sheets
    public function uploadDutySheet(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storePubliclyAs(
            "tenants/" . (auth()->user()->tenant_id ?? '0') . "/leads/{$lead->id}/duty_sheets",
            $fileName,
            'public'
        );

        $sheet = LeadDutySheet::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'lead_id' => $lead->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'file_name' => $fileName,
            'notes' => $data['notes'] ?? null,
        ]);
        try {
            $this->notificationService->create('Duty Sheet Uploaded', "Duty sheet uploaded for {$lead->lead_number}", ['lead_id' => $lead->id, 'duty_sheet_id' => $sheet->id], 'duty_sheet', 'low');
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Duty sheet uploaded.', 'data' => $sheet], 201);
    }
}
