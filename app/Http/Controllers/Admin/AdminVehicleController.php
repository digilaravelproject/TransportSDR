<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
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
        return view('admin.vehicles.show', compact('vehicle'));
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
