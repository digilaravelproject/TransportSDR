<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ManageShiftsController extends Controller
{
    /**
     * Display all shifts
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $search = $request->query('search');
        $status = $request->query('status');
        
        $query = Shift::query();
        
        if ($type && in_array($type, ['regular', 'overtime', 'night', 'custom'])) {
            $query->where('type', $type);
        }
        
        if ($status !== null) {
            $is_active = $status === 'active' ? true : false;
            $query->where('is_active', $is_active);
        }
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $shifts = $query->latest()->paginate(15);
        
        // Statistics
        $stats = [
            'total' => Shift::count(),
            'active' => Shift::active()->count(),
            'inactive' => Shift::inactive()->count(),
            'regular' => Shift::where('type', 'regular')->count(),
            'overtime' => Shift::where('type', 'overtime')->count(),
            'night' => Shift::where('type', 'night')->count(),
            'custom' => Shift::where('type', 'custom')->count(),
        ];
        
        return view('admin.shifts.index', compact('shifts', 'type', 'search', 'status', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $dayMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
        
        return view('admin.shifts.create', compact('dayMap'));
    }

    /**
     * Store new shift
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:shifts|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'type' => 'required|in:regular,overtime,night,custom',
            'days' => 'nullable|array',
            'is_active' => 'boolean',
            'max_drivers' => 'nullable|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')
                        ->with('success', 'Shift created successfully!');
    }

    /**
     * Show shift details
     */
    public function show(Shift $shift)
    {
        $shift->load(['drivers']);
        // $availableDrivers = \App\Models\Staff::where('user_id', '!=', null)->where('is_active', true)->where('staff_type', 'driver')->get();

        $availableDrivers = \App\Models\Staff::withoutGlobalScopes()->withTrashed()->where('user_id', '!=', null)->where('is_active', true)->where('staff_type', 'driver')->get();

        return view('admin.shifts.show', compact('shift', 'availableDrivers'));
    }

    /**
     * Show edit form
     */
    public function edit(Shift $shift)
    {
        $dayMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
        
        return view('admin.shifts.edit', compact('shift', 'dayMap'));
    }

    /**
     * Update shift
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:shifts,name,' . $shift->id . '|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'type' => 'required|in:regular,overtime,night,custom',
            'days' => 'nullable|array',
            'is_active' => 'boolean',
            'max_drivers' => 'nullable|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.show', $shift->id)
                        ->with('success', 'Shift updated successfully!');
    }

    /**
     * Delete shift
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('admin.shifts.index')
                        ->with('success', 'Shift deleted successfully!');
    }

    /**
     * Add driver to shift
     */
    public function addDriver(Request $request, Shift $shift)
    {
        $request->validate([
            'driver_id' => 'required|exists:staff,id',
        ]);
        
        if ($shift->drivers()->where('staff.id', $request->driver_id)->exists()) {
            return back()->with('error', 'Driver already assigned to this shift.');
        }

        if ($shift->max_drivers && $shift->drivers()->count() >= $shift->max_drivers) {
            return back()->with('error', 'Maximum drivers limit reached for this shift.');
        }

        $shift->drivers()->attach($request->driver_id);
        return back()->with('success', 'Driver assigned to shift successfully!');
    }

    /**
     * Remove driver from shift
     */
    public function removeDriver(Request $request, Shift $shift)
    {
        $request->validate([
            'driver_id' => 'required|exists:staff,id',
        ]);

        $shift->drivers()->detach($request->driver_id);
        return back()->with('success', 'Driver removed from shift successfully!');
    }
}
