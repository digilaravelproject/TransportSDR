<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleActivity;
use App\Models\VehicleDocument;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AdminVehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('tenant')->get();
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $tenants = Tenant::all();
        return view('admin.vehicles.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'registration_number' => 'required|string|max:255|unique:vehicles,registration_number',
            'type' => 'required|string|max:100',
            'seating_capacity' => 'nullable|integer',
            'model_year' => 'nullable|integer',
            'per_km_price' => 'nullable|numeric',
            'ac_price_per_km' => 'nullable|numeric',
            'rc_number' => 'nullable|string',
            'rc_expiry' => 'nullable|date',
            'insurance_number' => 'nullable|string',
            'insurance_expiry' => 'nullable|date',
            'permit_number' => 'nullable|string',
            'permit_expiry' => 'nullable|date',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // handle file uploads if present
        if ($request->hasFile('rc_file')) {
            $validated['rc_file'] = $request->file('rc_file')->store('vehicles', 'public');
        }
        if ($request->hasFile('insurance_file')) {
            $validated['insurance_file'] = $request->file('insurance_file')->store('vehicles', 'public');
        }
        if ($request->hasFile('permit_file')) {
            $validated['permit_file'] = $request->file('permit_file')->store('vehicles', 'public');
        }

        Vehicle::create($validated);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle created successfully');
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('tenant')->findOrFail($id);
        
        $activities = VehicleActivity::where('vehicle_id', $id)
            ->orderBy('activity_date', 'desc')
            ->get();
            
        $documents = VehicleDocument::where('vehicle_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.vehicles.show', compact('vehicle', 'activities', 'documents'));
    }

    public function storeFuel(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'activity_date' => 'required|date',
            'quantity'      => 'required|numeric|min:0',
            'price_per_unit'=> 'required|numeric|min:0',
            'amount'        => 'required|numeric|min:0',
            'station_name'  => 'nullable|string|max:255',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);

        $data = $request->only(['activity_date', 'quantity', 'price_per_unit', 'amount', 'station_name', 'notes']);
        $data['activity_type'] = 'fuel';
        $data['tenant_id'] = $vehicle->tenant_id;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store("tenants/{$vehicle->tenant_id}/vehicles/{$vehicle->id}/activities", 'public');
        }

        VehicleActivity::create($data);
        return back()->with('success', 'Fuel entry added successfully.');
    }

    public function storeService(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'activity_date' => 'required|date',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'amount_paid'   => 'nullable|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'km_reading'    => 'nullable|numeric|min:0',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);

        $data = $request->only(['activity_date', 'title', 'amount', 'amount_paid', 'workshop_name', 'km_reading', 'notes']);
        $data['activity_type'] = 'service';
        $data['tenant_id'] = $vehicle->tenant_id;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store("tenants/{$vehicle->tenant_id}/vehicles/{$vehicle->id}/activities", 'public');
        }

        VehicleActivity::create($data);
        return back()->with('success', 'Service entry added successfully.');
    }

    public function storeRepair(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'activity_date' => 'required|date',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'amount_paid'   => 'nullable|numeric|min:0',
            'garage_name'   => 'nullable|string|max:255',
            'km_reading'    => 'nullable|numeric|min:0',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);

        $data = $request->only(['activity_date', 'title', 'amount', 'amount_paid', 'garage_name', 'km_reading', 'notes']);
        $data['activity_type'] = 'repair';
        $data['tenant_id'] = $vehicle->tenant_id;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store("tenants/{$vehicle->tenant_id}/vehicles/{$vehicle->id}/activities", 'public');
        }

        VehicleActivity::create($data);
        return back()->with('success', 'Repair entry added successfully.');
    }

    public function uploadDocument(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'document_type' => 'required|string|max:100',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'alert_before_days' => 'nullable|integer|min:0',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only(['document_type', 'document_number', 'issue_date', 'expiry_date', 'alert_before_days', 'notes']);
        $data['tenant_id'] = $vehicle->tenant_id;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = auth()->id();
        $data['document_path'] = $request->file('file')->store("tenants/{$vehicle->tenant_id}/vehicles/{$vehicle->id}/documents", 'public');

        VehicleDocument::create($data);
        return back()->with('success', 'Document uploaded successfully.');
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $tenants = Tenant::all();
        return view('admin.vehicles.edit', compact('vehicle', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'registration_number' => 'required|string|max:255|unique:vehicles,registration_number,' . $id,
            'type' => 'required|string|max:100',
            'seating_capacity' => 'nullable|integer',
            'model_year' => 'nullable|integer',
            'per_km_price' => 'nullable|numeric',
            'ac_price_per_km' => 'nullable|numeric',
            'rc_number' => 'nullable|string',
            'rc_expiry' => 'nullable|date',
            'insurance_number' => 'nullable|string',
            'insurance_expiry' => 'nullable|date',
            'permit_number' => 'nullable|string',
            'permit_expiry' => 'nullable|date',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('rc_file')) {
            $validated['rc_file'] = $request->file('rc_file')->store('vehicles', 'public');
        }
        if ($request->hasFile('insurance_file')) {
            $validated['insurance_file'] = $request->file('insurance_file')->store('vehicles', 'public');
        }
        if ($request->hasFile('permit_file')) {
            $validated['permit_file'] = $request->file('permit_file')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully');
    }

    public function destroy($id)
    {
        Vehicle::findOrFail($id)->delete();
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted successfully');
    }
}
