<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class ManageVehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::orderBy('name')->get();
        return view('admin.vehicle-types', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('admin.vehicle-types-create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
            'price_per_km' => 'nullable|numeric',
            'ac_extra_price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'tenant_id' => 'nullable|exists:tenants,id'
        ]);
        // If authenticated user belongs to a tenant, ensure tenant_id is set
        if (auth()->check() && auth()->user()->tenant_id) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        VehicleType::create($data);
        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type created');
    }

    public function edit(VehicleType $vehicleType)
    {
        return view('admin.vehicle-types-edit', compact('vehicleType'));
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
            'price_per_km' => 'nullable|numeric',
            'ac_extra_price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $vehicleType->update($data);
        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type updated');
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type deleted');
    }
}
