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

            $routes = $query->get();

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
            $route = BusRoute::with('vehicles')->findOrFail($id);

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
}