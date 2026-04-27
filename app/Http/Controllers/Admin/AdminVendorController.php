<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Tenant;
use Illuminate\Http\Request;

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
        return view('admin.vendors.show', compact('vendor'));
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
