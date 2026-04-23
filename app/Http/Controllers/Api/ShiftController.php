<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;
use Exception;

class ShiftController extends Controller {
    /**
     * Get all drivers (for assignment)
     */
    public function driversList(Request $request)
    {
        // $drivers = \App\Models\Staff::where('user_id', '!=', null)->where('is_active', true)->where('staff_type', 'driver')->get();

        $drivers = \App\Models\Staff::withoutGlobalScopes()->withTrashed()->where('user_id', '!=', null)->where('is_active', true)->where('staff_type', 'driver')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Drivers list retrieved successfully',
            'data' => $drivers,
        ], 200);
    }

    /**
     * Add driver to shift
     */
    public function addDriver(Request $request, $shiftId)
    {
        $request->validate([
            'driver_id' => 'required|exists:staff,id',
        ]);
        $shift = Shift::findOrFail($shiftId);
        $driverId = $request->input('driver_id');
        // Check if already assigned
        if ($shift->drivers()->where('staff.id', $driverId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Driver already assigned to this shift',
            ], 409);
        }
        // Removed max_drivers check
        $shift->drivers()->attach($driverId);
        return response()->json([
            'success' => true,
            'message' => 'Driver added to shift successfully',
        ], 200);
    }

    /**
     * Remove driver from shift
     */
    public function removeDriver(Request $request, $shiftId)
    {
        $request->validate([
            'driver_id' => 'required|exists:staff,id',
        ]);
        $shift = Shift::findOrFail($shiftId);
        $driverId = $request->input('driver_id');
        if (!$shift->drivers()->where('staff.id', $driverId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not assigned to this shift',
            ], 404);
        }
        $shift->drivers()->detach($driverId);
        return response()->json([
            'success' => true,
            'message' => 'Driver removed from shift successfully',
        ], 200);
    }
    /**
     * Get all shifts with filtering by type
     */
    public function index(Request $request)
    {
        try {
            $type = $request->query('type', 'all');
            $limit = $request->query('limit', 15);
            $search = $request->query('search');
            
            $query = Shift::withCount('drivers');
            
            // Filter by type (all, regular, overtime, night, custom)
            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }
            
            // Search by name only
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            // Only active shifts
            $query->active();
            
            $shifts = $query->ordered()->paginate($limit);
            
            return response()->json([
                'success' => true,
                'message' => 'Shifts retrieved successfully',
                'data' => ShiftResource::collection($shifts),
                'pagination' => [
                    'total' => $shifts->total(),
                    'count' => $shifts->count(),
                    'per_page' => $shifts->perPage(),
                    'current_page' => $shifts->currentPage(),
                    'last_page' => $shifts->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving shifts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all shifts list (non-paginated)
     */
    public function list(Request $request)
    {
        try {
            $type = $request->query('type', 'all');
            $search = $request->query('search');
            
            $query = Shift::withCount('drivers');
            
            // Filter by type (all, regular, overtime, night, custom)
            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }
            
            // Search by name only
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            // Only active shifts
            $query->active();
            
            $shifts = $query->ordered()->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Shifts list retrieved successfully',
                'data' => ShiftResource::collection($shifts),
                'count' => $shifts->count(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving shifts list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single shift by ID
     */
    public function show($id)
    {
        try {
            $shift = Shift::with('drivers')->withCount('drivers')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Shift retrieved successfully',
                'data' => new ShiftResource($shift),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new shift (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:shifts|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'type' => 'required|in:regular,overtime,night,custom',
                'is_active' => 'boolean',
                'notes' => 'nullable|string',
                'date' => 'nullable|date',
            ]);

            $shift = Shift::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shift created successfully',
                'data' => new ShiftResource($shift),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating shift',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing shift (Admin only)
     */
    public function update(Request $request, $id)
    {
        try {
            $shift = Shift::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|unique:shifts,name,' . $id . '|max:255',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i',
                'type' => 'sometimes|in:regular,overtime,night,custom',
                'is_active' => 'boolean',
                'notes' => 'nullable|string',
                'date' => 'nullable|date',
            ]);

            $shift->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shift updated successfully',
                'data' => new ShiftResource($shift),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete shift (Admin only)
     */
    public function destroy($id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shift deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting shift',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search shifts
     */
    public function search(Request $request)
    {
        try {
            $search = $request->query('q', '');
            $type = $request->query('type', 'all');
            $limit = $request->query('limit', 10);

            $query = Shift::withCount('drivers');

            if ($search) {
                $query->searchByName($search);
            }

            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }

            $shifts = $query->active()->ordered()->limit($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'Shifts search completed',
                'data' => ShiftResource::collection($shifts),
                'count' => $shifts->count(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching shifts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get shift statistics
     */
    public function stats()
    {
        try {
            $totalShifts = Shift::count();
            $activeShifts = Shift::active()->count();
            $inactiveShifts = Shift::inactive()->count();
            
            $byType = Shift::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type');

            return response()->json([
                'success' => true,
                'message' => 'Shift statistics retrieved',
                'data' => [
                    'total_shifts' => $totalShifts,
                    'active_shifts' => $activeShifts,
                    'inactive_shifts' => $inactiveShifts,
                    'by_type' => $byType,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
