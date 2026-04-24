<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ManageRoutesController extends Controller
{
    /**
     * Display a listing of the routes.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        
        $query = Route::query()->withCount('vehicles');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status !== null && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }
        
        $routes = $query->latest()->paginate(15);
        
        return view('admin.routes.index', compact('routes', 'search', 'status'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create()
    {
        return view('admin.routes.create');
    }

    /**
     * Store a newly created route in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'points' => 'nullable|array',
            'points.*.type' => 'required_with:points|in:start,stop,end',
            'points.*.name' => 'required_with:points|string|max:255',
            'points.*.lat' => 'required_with:points|numeric',
            'points.*.lng' => 'required_with:points|numeric',
            'points.*.order' => 'required_with:points|integer',
            'schedules' => 'nullable|array',
            'schedules.*.departure_time' => 'required_with:schedules|string',
            'schedules.*.arrival_time' => 'required_with:schedules|string',
            'schedules.*.days' => 'nullable|array',
            'schedules.*.days.*' => 'string',
        ]);

        // Support legacy form fields (origin/destination/stops) by constructing `points`
        if (!isset($validated['points']) || empty($validated['points'])) {
            $points = [];
            if ($request->filled('origin')) {
                $points[] = [
                    'type' => 'start',
                    'name' => $request->input('origin'),
                    'lat' => $request->input('origin_lat'),
                    'lng' => $request->input('origin_lng'),
                    'order' => 0,
                ];
            }

            $stops = $request->input('stops', []);
            $order = 1;
            foreach ($stops as $stop) {
                $points[] = [
                    'type' => 'stop',
                    'name' => is_array($stop) ? ($stop['name'] ?? $stop) : $stop,
                    'lat' => is_array($stop) ? ($stop['lat'] ?? null) : null,
                    'lng' => is_array($stop) ? ($stop['lng'] ?? null) : null,
                    'order' => $order++,
                ];
            }

            if ($request->filled('destination')) {
                $points[] = [
                    'type' => 'end',
                    'name' => $request->input('destination'),
                    'lat' => $request->input('destination_lat'),
                    'lng' => $request->input('destination_lng'),
                    'order' => $order,
                ];
            }

            $validated['points'] = $points;
        }

        Route::create($validated);

        return redirect()->route('admin.routes.index')
                         ->with('success', 'Route created successfully!');
    }

    /**
     * Display the specified route.
     */
    public function show(Route $route)
    {
        $route->load('vehicles');
        // Fetch active/available vehicles to be assigned
        $availableVehicles = Vehicle::available()->get();
        // Prepare legacy variables for views that expect origin/destination/stops
        $origin = null;
        $destination = null;
        $stops = [];

        if (!empty($route->points) && is_array($route->points)) {
            $points = collect($route->points)->sortBy('order')->values();
            if ($points->count() > 0) {
                $first = $points->first();
                $last = $points->last();
                $origin = $first['name'] ?? null;
                $destination = $last['name'] ?? null;
                if ($points->count() > 2) {
                    $stops = $points->slice(1, $points->count() - 2)->values()->all();
                }
            }
        }

        return view('admin.routes.show', compact('route', 'availableVehicles', 'origin', 'destination', 'stops'));
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit(Route $route)
    {
        // Prepare legacy variables for the edit form
        $origin = null;
        $destination = null;
        $stops = [];

        if (!empty($route->points) && is_array($route->points)) {
            $points = collect($route->points)->sortBy('order')->values();
            if ($points->count() > 0) {
                $origin = $points->first()['name'] ?? null;
                $destination = $points->last()['name'] ?? null;
                if ($points->count() > 2) {
                    $stops = $points->slice(1, $points->count() - 2)->values()->all();
                }
            }
        }

        return view('admin.routes.edit', compact('route', 'origin', 'destination', 'stops'));
    }

    /**
     * Update the specified route in storage.
     */
    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'stops' => 'nullable|array',
            'stops.*' => 'string|max:255',
        ]);

        // Support legacy origin/destination/stops by building `points` if not provided
        if (!isset($validated['points']) || empty($validated['points'])) {
            $points = [];
            if ($request->filled('origin')) {
                $points[] = [
                    'type' => 'start',
                    'name' => $request->input('origin'),
                    'lat' => $request->input('origin_lat'),
                    'lng' => $request->input('origin_lng'),
                    'order' => 0,
                ];
            }

            $stops = $request->input('stops', []);
            $order = 1;
            foreach ($stops as $stop) {
                $points[] = [
                    'type' => 'stop',
                    'name' => is_array($stop) ? ($stop['name'] ?? $stop) : $stop,
                    'lat' => is_array($stop) ? ($stop['lat'] ?? null) : null,
                    'lng' => is_array($stop) ? ($stop['lng'] ?? null) : null,
                    'order' => $order++,
                ];
            }

            if ($request->filled('destination')) {
                $points[] = [
                    'type' => 'end',
                    'name' => $request->input('destination'),
                    'lat' => $request->input('destination_lat'),
                    'lng' => $request->input('destination_lng'),
                    'order' => $order,
                ];
            }

            $validated['points'] = $points;
        }

        $route->update($validated);

        return redirect()->route('admin.routes.index')
                         ->with('success', 'Route updated successfully!');
    }

    /**
     * Remove the specified route from storage.
     */
    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->route('admin.routes.index')
                         ->with('success', 'Route deleted successfully!');
    }

    /**
     * Assign a vehicle to the route.
     */
    public function addVehicle(Request $request, Route $route)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        if ($route->vehicles()->where('vehicles.id', $request->vehicle_id)->exists()) {
            return back()->with('error', 'Vehicle is already assigned to this route.');
        }

        $route->vehicles()->attach($request->vehicle_id);

        return back()->with('success', 'Vehicle assigned successfully!');
    }

    /**
     * Remove a vehicle from the route.
     */
    public function removeVehicle(Request $request, Route $route)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $route->vehicles()->detach($request->vehicle_id);

        return back()->with('success', 'Vehicle removed from route successfully!');
    }
}
