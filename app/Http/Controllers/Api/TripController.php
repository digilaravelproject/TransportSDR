<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\{StoreTripRequest, UpdateTripRequest, AddPaymentRequest};
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private TripService $service) {}

    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $user  = auth()->user();
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
    }

    public function store(StoreTripRequest $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $trip = $this->service->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Trip {$trip->trip_number} created successfully.",
            'data'    => new TripResource($trip),
        ], 201);
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

        $trip->load(['vehicle', 'customer', 'driver', 'helper', 'payments']);

        return response()->json([
            'success' => true,
            'data'    => new TripResource($trip),
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $trip = $this->service->update($trip, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Trip updated successfully.',
            'data'    => new TripResource($trip),
        ]);
    }

    public function destroy(Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin']);

        abort_if(
            $trip->status === 'ongoing',
            422,
            'Cannot delete an ongoing trip.'
        );

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted successfully.',
        ]);
    }

    public function updateKm(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

        $data = $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km'   => 'required|numeric|min:0|gte:start_km',
        ]);

        $trip->update($data);
        $trip->refresh();

        return response()->json([
            'success' => true,
            'message' => "KM updated. Total: {$trip->total_km} km | Grade: {$trip->km_grade}",
            'data'    => new TripResource($trip),
        ]);
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

        $data = $request->validate([
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        if ($data['status'] === 'completed') {
            $trip = $this->service->complete($trip);
        } else {
            $trip->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => "Trip status updated to: {$trip->fresh()->status}",
            'data'    => new TripResource($trip->fresh()),
        ]);
    }

    public function addPayment(AddPaymentRequest $request, Trip $trip)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $payment = $this->service->addPayment($trip, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Payment of ₹{$payment->amount} recorded successfully.",
            'data'    => new TripResource(
                $trip->fresh(['vehicle', 'customer', 'driver', 'payments'])
            ),
        ], 201);
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

    // Role check helper
    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
