<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route as BusRoute;
use App\Models\Vehicle;
use Exception;

class RouteController extends Controller
{
    /**
     * 1. Add Route
     */
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'name' => 'required|string',
                'distance' => 'required|numeric',
                'estimated_time' => 'required|string',
                'points' => 'required|array|min:1',
                'points.*.type' => 'required|in:start,stop,end',
                'points.*.name' => 'required|string',
                'points.*.lat' => 'required|numeric',
                'points.*.lng' => 'required|numeric',
                'points.*.order' => 'required|integer',
                'schedules' => 'nullable|array',
                'schedules.*.departure_time' => 'required_with:schedules|string',
                'schedules.*.arrival_time' => 'required_with:schedules|string',
                'schedules.*.days' => 'nullable|array',
                'schedules.*.days.*' => 'string',
            ]);

            $route = BusRoute::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Route created successfully',
                'data' => $route
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create route',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Get Routes Listing (with type=all)
     */
    public function index(Request $request)
    {
        try {
            $type = $request->query('type', 'all');

            $query = BusRoute::query();

            if ($type !== 'all') {
                $query->where('status', $type);
            }

            $routes = $query->with(['vehicles', 'drivers'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Routes fetched successfully',
                'data' => $routes
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch routes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Search Route
     */
    public function search(Request $request)
    {
        try {
            $q = $request->query('query');

            $routes = BusRoute::query()
                ->where(function ($query) use ($q) {

                    // Search in normal columns
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('distance', 'like', "%{$q}%")
                        ->orWhere('estimated_time', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%");

                    // Search in JSON columns (MySQL)
                    $query->orWhereRaw("JSON_SEARCH(points, 'one', ?) IS NOT NULL", [$q])
                        ->orWhereRaw("JSON_SEARCH(schedules, 'one', ?) IS NOT NULL", [$q]);

                })
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $routes
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to search routes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. Get Route by ID (with assigned vehicles)
     */
    public function show($id)
    {
        try {
            $route = BusRoute::with(['vehicles', 'drivers'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Route fetched successfully',
                'data' => $route
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 5. Update Route
     */
    public function update(Request $request, $id)
    {
        try {
            $route = BusRoute::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|string',
                'distance' => 'sometimes|numeric',
                'estimated_time' => 'sometimes|string',
                'points' => 'nullable|array',
                'points.*.type' => 'required_with:points|in:start,stop,end',
                'points.*.name' => 'required_with:points|string',
                'points.*.lat' => 'required_with:points|numeric',
                'points.*.lng' => 'required_with:points|numeric',
                'points.*.order' => 'required_with:points|integer',
                'schedules' => 'nullable|array',
                'schedules.*.departure_time' => 'required_with:schedules|string',
                'schedules.*.arrival_time' => 'required_with:schedules|string',
                'schedules.*.days' => 'nullable|array',
                'schedules.*.days.*' => 'string',
            ]);

            $route->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Route updated successfully',
                'data' => $route
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update route',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. Assign Multiple Vehicles to Route
     */
    public function assignVehicles(Request $request, $id)
    {
        try {
            $route = BusRoute::findOrFail($id);

            $vehicleIds = $request->input('vehicle_ids', []);

            $route->vehicles()->sync($vehicleIds);

            return response()->json([
                'success' => true,
                'message' => 'Vehicles assigned successfully'
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign vehicles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simple permission helper copied from other controllers
     */
    private function checkRole(array $roles): void
    {
        if (! auth()->user()?->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }

    /**
     * Get drivers NOT assigned to any route at given date (defaults to today)
     */
    public function availableDrivers(Request $request, $route_id)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

            $checkDate = $request->date ? \Carbon\Carbon::parse($request->date)->toDateString() : now()->toDateString();

            // Drivers assigned to any route overlapping this date
            $assignedDriverIds = \DB::table('route_driver')
                ->where(function ($q) use ($checkDate) {
                    $q->whereDate('assigned_from', '<=', $checkDate)
                        ->where(function ($q2) use ($checkDate) {
                            $q2->whereDate('assigned_to', '>=', $checkDate)
                            ->orWhereNull('assigned_to');
                        });
                })->pluck('driver_id')->toArray();

            // Drivers who are on trips overlapping this date
            $onTripDriverIds = \DB::table('trips')
                ->where(function ($q) use ($checkDate) {
                    $q->whereDate('trip_date', '<=', $checkDate)
                        ->where(function ($q2) use ($checkDate) {
                            $q2->whereDate('return_date', '>=', $checkDate)
                                ->orWhereNull('return_date');
                        });
                })
                ->where('status', '!=', 'cancelled')
                ->pluck('driver_id')
                ->toArray();

            $excludeIds = array_unique(array_filter(array_merge($assignedDriverIds, $onTripDriverIds)));

            $driversQuery = \App\Models\Staff::query()
                ->where(function ($q) {
                    $q->whereHas('role', fn($qr) => $qr->where('name', 'driver'))
                        ->orWhere('staff_type', 'driver');
                })
                ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
                ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
                ->where('is_active', true)
                ->latest();

            $drivers = $driversQuery->paginate($request->per_page ?? 20)->withQueryString();

            return response()->json([
                'success' => true,
                'message' => 'Available drivers retrieved successfully',
                'data'    => \App\Http\Resources\StaffResource::collection($drivers),
                'meta'    => [
                    'total' => $drivers->total(),
                    'current_page' => $drivers->currentPage(),
                    'last_page' => $drivers->lastPage(),
                ],
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to view drivers.', 'error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch available drivers.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Assign drivers to route (with optional assigned_from / assigned_to dates)
     */
    public function assignDrivers(Request $request, $route_id)
    {
        try {
            $this->checkRole(['superadmin', 'admin']);

            $data = $request->validate([
                'driver_ids' => 'required|array|min:1',
                'driver_ids.*' => 'exists:staff,id',
                'assigned_from' => 'nullable|date',
                'assigned_to' => 'nullable|date|after_or_equal:assigned_from',
            ]);

            $route = BusRoute::findOrFail($route_id);

            $from = $data['assigned_from'] ?? null;
            $to   = $data['assigned_to'] ?? null;

            $conflicts = [];
            $attached = [];

            foreach ($data['driver_ids'] as $driverId) {
                // check overlap with existing assignments for this driver
                $overlap = \DB::table('route_driver')
                    ->where('driver_id', $driverId)
                    ->where(function ($q) use ($from, $to) {
                        if ($from && $to) {
                            $q->where(function ($s) use ($from, $to) {
                                $s->whereDate('assigned_from', '<=', $to)
                                    ->where(function ($s2) use ($from) {
                                        $s2->whereDate('assigned_to', '>=', $from)
                                            ->orWhereNull('assigned_to');
                                    });
                            });
                        }
                    })->exists();

                if ($overlap) {
                    $conflicts[] = $driverId;
                    continue;
                }

                // attach with pivot attributes
                $route->drivers()->syncWithoutDetaching([$driverId => ['assigned_from' => $from, 'assigned_to' => $to]]);
                $attached[] = $driverId;
            }

            return response()->json(['success' => true, 'message' => 'Driver assignment completed', 'data' => ['attached' => $attached, 'conflicts' => $conflicts]]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign drivers', 'error' => $e->getMessage()], 500);
        }
    }
}