<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Staff, StaffAttendance, StaffSalary, StaffAdvance, StaffDaLog, Trip, Shift};
use App\Services\StaffService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(private StaffService $service) {}

    // 1. LIST PAGE
    public function index(Request $request)
    {
        $shifts = Shift::active()->get();
        // Assuming you have a Role model for staff_type
        $roles = \DB::table('role_modules')->get();

        $staff = Staff::with(['user', 'role', 'shift'])
            ->when($request->staff_type,   fn($q, $v) => $q->where('staff_type', $v))
            ->when($request->work_shift,   fn($q, $v) => $q->where('work_shift', $v))
            ->when($request->search,       fn($q, $v) => $q->where('name', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.staff.index', compact('staff', 'shifts', 'roles'));
    }

    // 2. CREATE PAGE
    public function create()
    {
        $shifts = Shift::active()->get();
        $roles = \DB::table('role_modules')->get();
        return view('admin.staff.create', compact('shifts', 'roles'));
    }

    // 3. STORE RECORD
    // public function store(Request $request)
    // {
    //     // Add basic validations (Same as API)
    //     $data = $request->validate([
    //         'name'            => 'required|string|max:255',
    //         'phone'           => 'required|string|max:15',
    //         'staff_type'      => 'required|exists:role_modules,id',
    //         'work_shift'      => 'nullable|exists:shifts,id',
    //         'basic_salary'    => 'nullable|numeric|min:0',
    //         // ... (baki validations add karein)
    //     ]);

    //     try {
    //         $this->service->storeWithDocuments($data, $request);
    //         return redirect()->route('admin.staff.index')->with('success', 'Staff added successfully.');
    //     } catch (\Exception $e) {
    //         return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }
    // 3. STORE RECORD
    public function store(Request $request)
    {
        // 100% Complete Validation (Same as API)
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'phone'                  => 'required|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'required|exists:role_modules,id',
            'salary_type'            => 'nullable|in:monthly,daily',
            'work_shift'             => 'nullable|exists:shifts,id',
            'basic_salary'           => 'nullable|numeric|min:0',
            'address'                => 'nullable|string',
            'date_of_joining'        => 'nullable|date',

            // Document Fields
            'aadhar_number'          => 'nullable|string|max:50',
            'aadhar_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'pan_number'             => 'nullable|string|max:50',
            'pan_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dl_number'              => 'nullable|string|max:50',
            'dl_expiry'              => 'nullable|date',
            'dl_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'badge_number'           => 'nullable|string|max:50',
            'badge_expiry'           => 'nullable|date',
            'badge_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'passbook_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Service call jo text data aur files dono ko database me save karegi
            $this->service->storeWithDocuments($data, $request);

            return redirect()->route('admin.staff.index')->with('success', 'Staff added successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 6. UPDATE RECORD
    public function update(Request $request, Staff $staff)
    {
        // 100% Complete Validation (Same as API)
        $data = $request->validate([
            'name'                   => 'sometimes|string|max:255',
            'phone'                  => 'sometimes|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'sometimes|exists:role_modules,id',
            'salary_type'            => 'nullable|in:monthly,daily',
            'work_shift'             => 'nullable|exists:shifts,id',
            'assigned_vehicle'       => 'nullable|string|max:100',
            'basic_salary'           => 'nullable|numeric|min:0',
            'address'                => 'nullable|string',
            'date_of_joining'        => 'nullable|date',
            'is_available'           => 'boolean',
            'is_active'              => 'boolean',

            // Document Fields
            'aadhar_number'          => 'nullable|string|max:50',
            'aadhar_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'pan_number'             => 'nullable|string|max:50',
            'pan_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dl_number'              => 'nullable|string|max:50',
            'dl_expiry'              => 'nullable|date',
            'dl_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'badge_number'           => 'nullable|string|max:50',
            'badge_expiry'           => 'nullable|date',
            'badge_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'passbook_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Service call jo text data aur files dono update karegi
            $this->service->updateWithDocuments($staff, $data, $request);

            return redirect()->route('admin.staff.show', $staff->id)->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 4. SHOW FULL PROFILE (DASHBOARD FOR SINGLE STAFF)
    public function show(Staff $staff)
    {
        $staff->load(['user', 'role', 'shift', 'documents']);

        // Profile tabs ke liye saara data yahi load kar lenge
        $dutyLogs = StaffAttendance::where('staff_id', $staff->id)->latest('date')->take(30)->get();
        $salaries = StaffSalary::where('staff_id', $staff->id)->latest('year')->latest('month')->get();
        $advances = StaffAdvance::where('staff_id', $staff->id)->latest()->get();

        $trips = Trip::where(function ($q) use ($staff) {
            $q->where('driver_id', $staff->id)->orWhere('helper_id', $staff->id);
        })->latest('trip_date')->take(10)->get();

        return view('admin.staff.show', compact('staff', 'dutyLogs', 'salaries', 'advances', 'trips'));
    }

    // 5. EDIT PAGE
    public function edit(Staff $staff)
    {
        $shifts = Shift::active()->get();
        $roles = \DB::table('role_modules')->get();
        return view('admin.staff.edit', compact('staff', 'shifts', 'roles'));
    }

    // 6. UPDATE RECORD
    // public function update(Request $request, Staff $staff)
    // {
    //     $data = $request->validate([
    //         'name'       => 'sometimes|string|max:255',
    //         'phone'      => 'sometimes|string|max:15',
    //         'staff_type' => 'sometimes|exists:role_modules,id',
    //         'work_shift' => 'nullable|exists:shifts,id',
    //     ]);

    //     try {
    //         $this->service->updateWithDocuments($staff, $data, $request);
    //         return redirect()->route('admin.staff.show', $staff->id)->with('success', 'Staff updated successfully.');
    //     } catch (\Exception $e) {
    //         return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }

    // 7. TOGGLE STATUS (Quick action from web)
    public function toggleStatus(Staff $staff)
    {
        $staff->update(['is_active' => !$staff->is_active]);
        return back()->with('success', 'Staff status updated.');
    }

    // 8. DELETE RECORD
    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Staff deleted successfully.');
    }
}
