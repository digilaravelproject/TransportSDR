<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\Tenant;
use App\Models\Staff;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('tenant')->get();
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        $tenants = Tenant::all();
        return view('admin.vendors.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'vendor_name' => 'required|string|max:255',
            'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'monthly_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        Vendor::create($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully');
    }

    public function show($id)
    {
        $vendor = Vendor::with(['vehicles', 'bills', 'tenant'])->findOrFail($id);
        $assignedDriverIds = DB::table('vendor_staff')->where('vendor_id', $vendor->id)->pluck('staff_id')->toArray();
        $assignedDrivers = Staff::whereIn('id', $assignedDriverIds)->get();

        $availableDrivers = Staff::where(function($q) {
                $q->where('staff_type', 4)->orWhere('work_shift', 'driver');
            })
            ->whereNotIn('id', $assignedDriverIds)
            ->get();

        $assignedVehicleIds = DB::table('vendor_vehicle')->where('vendor_id', $vendor->id)->pluck('vehicle_id')->toArray();
        $availableVehicles = Vehicle::whereNotIn('id', $assignedVehicleIds)->get();

        return view('admin.vendors.show', compact('vendor', 'assignedDrivers', 'availableDrivers', 'availableVehicles'));
    }

    public function assignVehicles(Request $request, Vendor $vendor)
    {
        $request->validate(['vehicle_ids' => 'required|array']);
        foreach ($request->vehicle_ids as $vehicleId) {
            DB::table('vendor_vehicle')->updateOrInsert(
                ['vendor_id' => $vendor->id, 'vehicle_id' => $vehicleId],
                ['tenant_id' => $vendor->tenant_id, 'assigned_by' => auth()->id(), 'updated_at' => now(), 'created_at' => now()]
            );
        }
        return back()->with('success', 'Vehicles assigned');
    }

    public function removeVehicle(Vendor $vendor, Vehicle $vehicle)
    {
        DB::table('vendor_vehicle')->where('vendor_id', $vendor->id)->where('vehicle_id', $vehicle->id)->delete();
        return back()->with('success', 'Vehicle removed from vendor');
    }

    public function assignDrivers(Request $request, Vendor $vendor)
    {
        $request->validate(['staff_ids' => 'required|array']);
        foreach ($request->staff_ids as $staffId) {
            DB::table('vendor_staff')->updateOrInsert(
                ['vendor_id' => $vendor->id, 'staff_id' => $staffId],
                ['tenant_id' => $vendor->tenant_id, 'assigned_by' => auth()->id(), 'updated_at' => now(), 'created_at' => now()]
            );
        }
        return back()->with('success', 'Drivers assigned');
    }

    public function removeDriver(Vendor $vendor, Staff $staff)
    {
        DB::table('vendor_staff')->where('vendor_id', $vendor->id)->where('staff_id', $staff->id)->delete();
        return back()->with('success', 'Driver removed from vendor');
    }

    public function addBill(Request $request, Vendor $vendor)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_date' => 'required|date',
            'status' => 'nullable|in:pending,paid',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->only(['invoice_number', 'amount', 'billing_date', 'status']);
        $data['tenant_id'] = $vendor->tenant_id;
        $data['vendor_id'] = $vendor->id;
        $data['created_by'] = auth()->id();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store("tenants/{$vendor->tenant_id}/vendors/{$vendor->id}/bills", 'public');
        }

        VendorBill::create($data);
        return back()->with('success', 'Bill added');
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $tenants = Tenant::all();
        return view('admin.vendors.edit', compact('vendor', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'vendor_name' => 'required|string|max:255',
            'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'monthly_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $vendor->update($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully');
    }

    public function destroy($id)
    {
        Vendor::findOrFail($id)->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully');
    }
}
