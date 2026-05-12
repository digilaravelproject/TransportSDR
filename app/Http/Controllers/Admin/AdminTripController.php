<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Staff;
use App\Services\TripService;
use Illuminate\Http\Request;

class AdminTripController extends Controller
{
    public function __construct(private TripService $service) {}

    public function index(Request $request)
    {
        $query = Trip::with(['vehicle', 'customer', 'driver'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, function ($q, $v) {
                $q->where(function ($qq) use ($v) {
                    $qq->where('trip_number', 'like', "%{$v}%")
                        ->orWhere('customer_name', 'like', "%{$v}%")
                        ->orWhere('trip_route', 'like', "%{$v}%");
                });
            });

        $trips = $query->latest('trip_date')->paginate(20)->withQueryString();

        return view('admin.trips.index', compact('trips'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['vehicle', 'customer', 'driver', 'helper', 'payments']);
        return view('admin.trips.show', compact('trip'));
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        if ($data['status'] === 'completed') {
            $this->service->complete($trip);
        } else {
            $trip->update($data);
        }

        return back()->with('success', 'Trip status updated to ' . $data['status']);
    }

    public function updateKm(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km'   => 'required|numeric|min:0|gte:start_km',
        ]);

        $trip->update($data);
        return back()->with('success', 'Trip KM updated successfully');
    }

    public function addPayment(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|string',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $this->service->addPayment($trip, $data);
        return back()->with('success', 'Payment recorded successfully');
    }

    public function destroy(Trip $trip)
    {
        if ($trip->status === 'ongoing') {
            return back()->with('error', 'Cannot delete an ongoing trip.');
        }

        $trip->delete();
        return redirect()->route('admin.trips.index')->with('success', 'Trip deleted successfully.');
    }
}
