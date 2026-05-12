<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\{StoreTripRequest, UpdateTripRequest, AddPaymentRequest};
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Staff;
use App\Models\TripExpense;
use App\Models\RoleModule;
use App\Models\TripDutySheet;
use App\Services\TripService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;
use Exception;

class TripController extends Controller
{
    public function __construct(private TripService $service, private NotificationService $notificationService) {}

    // GET /api/v1/trips/vehicles/list
    public function vehicleList_old()
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
    
    // GET /api/v1/trips/vehicles/list
    public function vehicleList()
    {
        $vehicles = Vehicle::select(
                'id',
                'registration_number',
                'type',
                'seating_capacity',
                'is_available'
            )
            ->get()
            ->map(function ($v) {
    
                // If type is integer, get name from vehicle_types table
                if (is_numeric($v->type)) {
                    $vehicleType = \DB::table('vehicle_types')
                        ->where('id', $v->type)
                        ->value('name');
    
                    $type = $vehicleType ?? $v->type;
                } else {
                    // If already string, use directly
                    $type = $v->type;
                }
    
                return [
                    'id' => $v->id,
                    'registration_number' => $v->registration_number,
                    'type' => $type,
                    'seating_capacity' => $v->seating_capacity,
                    'status' => $v->is_available ? 'available' : 'on_trip'
                ];
            });
    
        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    // GET /api/v1/trips/drivers/list
    public function driverList_old()
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
    
    // GET /api/v1/trips/drivers/list
    public function driverList()
    {
        $tenantId = auth()->user()->tenant_id;
    
        // Get driver role id for current tenant
        $driverRole = RoleModule::where('tenant_id', $tenantId)
            ->whereIn('name', ['Driver', 'driver'])
            ->first();
    
        // If role not found
        if (!$driverRole) {
            return response()->json([
                'success' => false,
                'message' => 'Driver role not found'
            ]);
        }
    
        // Get drivers
        $drivers = Staff::where('tenant_id', $tenantId)
            ->where('staff_type', $driverRole->id)
            ->whereNull('deleted_at') // not trashed
            ->select('id', 'name', 'phone', 'is_available')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'phone' => $d->phone,
                'status' => $d->is_available ? 'available' : 'on_trip'
            ]);
    
        return response()->json([
            'success' => true,
            'data' => $drivers
        ]);
    }

    public function index_old(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $user  = auth()->user();

        try {
            $query = Trip::with(['vehicle', 'customer', 'driver'])
                ->when($request->status,     fn($q, $v) => $q->where('status', $v))
                ->when($request->from,       fn($q, $v) => $q->whereDate('trip_date', '>=', $v))
                ->when($request->to,         fn($q, $v) => $q->whereDate('trip_date', '<=', $v))
                ->when($request->driver_id,  fn($q, $v) => $q->where('driver_id', $v))
                ->when($request->search,     fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('trip_number',   'like', "%{$v}%")
                        ->orWhere('customer_name', 'like', "%{$v}%")
                        ->orWhere('trip_route',    'like', "%{$v}%");
                }));

            // Driver sirf apni trips dekh sakta hai
            if ($user->isDriver()) {
                $query->where('driver_id', $user->staff?->id);
            }

            $trips = $query->latest('trip_date')
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'data'    => TripResource::collection($trips),
                'meta'    => [
                    'total'        => $trips->total(),
                    'current_page' => $trips->currentPage(),
                    'last_page'    => $trips->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching trips.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $user = auth()->user();

        try {

            // Base query for counts
            $baseQuery = Trip::query();

            // Driver can only see own trips
            if ($user->isDriver()) {
                $baseQuery->where('driver_id', $user->staff?->id);
            }

            // Counts
            $totalTrips = (clone $baseQuery)->count();

            $ongoingTrips = (clone $baseQuery)
                ->where('status', 'ongoing')
                ->count();

            $pendingTrips = (clone $baseQuery)
                ->where('status', 'pending')
                ->count();

            // Main listing query
            $query = Trip::with(['vehicle', 'customer', 'driver', 'vehicleTypeDetails'])
                ->when($request->status, fn($q, $v) =>
                    $q->where('status', $v)
                )
                ->when($request->from, fn($q, $v) =>
                    $q->whereDate('trip_date', '>=', $v)
                )
                ->when($request->to, fn($q, $v) =>
                    $q->whereDate('trip_date', '<=', $v)
                )
                ->when($request->driver_id, fn($q, $v) =>
                    $q->where('driver_id', $v)
                )
                ->when($request->search, function ($q, $v) {
                    $q->where(function ($qq) use ($v) {
                        $qq->where('trip_number', 'like', "%{$v}%")
                            ->orWhere('customer_name', 'like', "%{$v}%")
                            ->orWhere('trip_route', 'like', "%{$v}%");
                    });
                });

            // Driver can only see own trips
            if ($user->isDriver()) {
                $query->where('driver_id', $user->staff?->id);
            }

            $trips = $query->latest('trip_date')
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,

                // Trip Counts
                'trip_summary' => [
                    'total_trip'   => $totalTrips,
                    'ongoing_trip' => $ongoingTrips,
                    'pending_trip' => $pendingTrips,
                ],

                // Trip List
                'data' => TripResource::collection($trips),

                // Pagination
                'meta' => [
                    'total'        => $trips->total(),
                    'current_page' => $trips->currentPage(),
                    'last_page'    => $trips->lastPage(),
                    'per_page'     => $trips->perPage(),
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching trips.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreTripRequest $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        try {
            $data = $request->validated();
            // If client sent 'points' (lead-style objects), convert to destination names
            if (!empty($data['points']) && is_array($data['points'])) {
                // Preserve full point objects (type,name,lat,lng,order)
                $data['destination_points'] = $data['points'];
                unset($data['points']);
            }

            $trip = $this->service->store($data);
            try {
                $this->notificationService->create('New Trip: ' . $trip->customer_name, "Trip {$trip->trip_number} created", ['trip_id' => $trip->id], 'trip', 'high');
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => "Trip {$trip->trip_number} created successfully.",
                'data'    => new TripResource($trip),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the trip.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $user = auth()->user();

        // Driver sirf apni trip dekh sakta hai
        if ($user->isDriver() && $trip->driver_id !== $user->staff?->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this trip.',
            ], 403);
        }

        try {
            $trip->load(['vehicle', 'customer', 'driver', 'helper', 'payments']);

            return response()->json([
                'success' => true,
                'data'    => new TripResource($trip),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching trip details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        try {
            $data = $request->validated();
            if (!empty($data['points']) && is_array($data['points'])) {
                // Preserve full point objects on update as well
                $data['destination_points'] = $data['points'];
                unset($data['points']);
            }

            $trip = $this->service->update($trip, $data);

            return response()->json([
                'success' => true,
                'message' => 'Trip updated successfully.',
                'data'    => new TripResource($trip),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the trip.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin']);

        abort_if(
            $trip->status === 'ongoing',
            422,
            'Cannot delete an ongoing trip.'
        );

        try {
            $trip->delete();

            try {
                $this->notificationService->create('Trip Deleted', "Trip {$trip->trip_number} deleted", ['trip_id' => $trip->id], 'trip', 'medium');
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Trip deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the trip.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateKm(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

        $data = $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km'   => 'required|numeric|min:0|gte:start_km',
        ]);

        try {
            $trip->update($data);
            $trip->refresh();

            return response()->json([
                'success' => true,
                'message' => "KM updated. Total: {$trip->total_km} km | Grade: {$trip->km_grade}",
                'data'    => new TripResource($trip),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating KM.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

        $data = $request->validate([
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        try {
            if ($data['status'] === 'completed') {
                $trip = $this->service->complete($trip);
                try {
                    $this->notificationService->create('Trip Completed', "Trip {$trip->trip_number} completed", ['trip_id' => $trip->id], 'trip', 'high');
                } catch (\Throwable $e) {}
            } else {
                $trip->update($data);
                if ($data['status'] === 'ongoing') {
                    try {
                        $this->notificationService->create('Trip Started', "Trip {$trip->trip_number} started", ['trip_id' => $trip->id], 'trip', 'high');
                    } catch (\Throwable $e) {}
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Trip status updated to: {$trip->fresh()->status}",
                'data'    => new TripResource($trip->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating trip status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function addPayment(AddPaymentRequest $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $payment = $this->service->addPayment($trip, $request->validated());

            try {
                $this->notificationService->create('Payment Received', "Payment of ₹{$payment->amount} for {$trip->trip_number}", ['trip_id' => $trip->id, 'amount' => $payment->amount], 'payment', 'high');
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹{$payment->amount} recorded successfully.",
                'data'    => new TripResource(
                    $trip->fresh(['vehicle', 'customer', 'driver', 'payments'])
                ),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the payment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function invoice(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $absolutePath = $this->service->generateInvoice($trip);

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file generate nahi hui.',
                ], 500);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-' . $trip->trip_number . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }

    public function dutySlip(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        try {
            $absolutePath = $this->service->generateDutySlip($trip);

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duty slip generate nahi hui.',
                ], 500);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="duty-slip-' . $trip->trip_number . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }

    // POST /api/v1/trips/{trip}/assign-vehicles
    public function assignVehicles(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'integer|exists:vehicles,id',
        ]);

        $ids = array_values(array_unique($data['vehicle_ids']));

        try {
            // Release previously assigned vehicles that are not in new list
            if (!empty($trip->assigned_vehicles) && is_array($trip->assigned_vehicles)) {
                $toRelease = array_diff($trip->assigned_vehicles, $ids);
                if (!empty($toRelease)) Vehicle::whereIn('id', $toRelease)->update(['is_available' => true]);
            }

            // Mark new vehicles as not available
            Vehicle::whereIn('id', $ids)->update(['is_available' => false]);

            $trip->assigned_vehicles = $ids;
            $trip->save();

            try { $this->notificationService->create('Vehicles Assigned', "Vehicles assigned to {$trip->trip_number}", ['trip_id' => $trip->id], 'assign', 'medium'); } catch (\Throwable $e) {}

            return response()->json(['success' => true, 'message' => 'Vehicles assigned.', 'data' => new TripResource($trip->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign vehicles.', 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/v1/trips/{trip}/assign-drivers
    public function assignDrivers(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'driver_ids' => 'required|array|min:1',
            'driver_ids.*' => 'integer|exists:staff,id',
        ]);

        $ids = array_values(array_unique($data['driver_ids']));

        try {
            if (!empty($trip->assigned_drivers) && is_array($trip->assigned_drivers)) {
                $toRelease = array_diff($trip->assigned_drivers, $ids);
                if (!empty($toRelease)) Staff::whereIn('id', $toRelease)->update(['is_available' => true]);
            }

            Staff::whereIn('id', $ids)->update(['is_available' => false]);

            $trip->assigned_drivers = $ids;
            $trip->save();

            try { $this->notificationService->create('Drivers Assigned', "Drivers assigned to {$trip->trip_number}", ['trip_id' => $trip->id], 'assign', 'medium'); } catch (\Throwable $e) {}

            return response()->json(['success' => true, 'message' => 'Drivers assigned.', 'data' => new TripResource($trip->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign drivers.', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/v1/trips/{trip}/expenses
    public function expenses(Trip $trip)
    {
        $items = TripExpense::where('trip_id', $trip->id)->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/v1/trips/{trip}/expenses
    public function addExpense(Request $request, Trip $trip)
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
                "tenants/" . (auth()->user()->tenant_id ?? '0') . "/trips/{$trip->id}/receipts",
                time() . '_' . $file->getClientOriginalName(),
                'public'
            );
        }

        $expense = TripExpense::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'trip_id' => $trip->id,
            'category' => $data['category'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'entry_date' => $data['entry_date'] ?? now()->toDateString(),
            'receipt_path' => $path,
            'created_by' => auth()->id(),
        ]);

        try { $this->notificationService->create('Expense Added', "Expense of {$expense->amount} added to {$trip->trip_number}", ['trip_id' => $trip->id, 'expense_id' => $expense->id], 'expense', 'low'); } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Expense added.', 'data' => $expense], 201);
    }

    // GET /api/v1/trips/{trip}/duty-sheets
    public function dutySheets(Trip $trip)
    {
        $items = TripDutySheet::where('trip_id', $trip->id)->with('uploader')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/v1/trips/{trip}/duty-sheets
    public function uploadDutySheet(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storePubliclyAs(
            "tenants/" . (auth()->user()->tenant_id ?? '0') . "/trips/{$trip->id}/duty_sheets",
            $fileName,
            'public'
        );

        $sheet = TripDutySheet::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'trip_id' => $trip->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'file_name' => $fileName,
            'notes' => $data['notes'] ?? null,
        ]);

        try { $this->notificationService->create('Duty Sheet Uploaded', "Duty sheet uploaded for {$trip->trip_number}", ['trip_id' => $trip->id, 'duty_sheet_id' => $sheet->id], 'duty_sheet', 'low'); } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Duty sheet uploaded.', 'data' => $sheet], 201);
    }

    // Role check helper
    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
