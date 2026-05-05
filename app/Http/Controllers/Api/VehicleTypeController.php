<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleTypeResource;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $query = VehicleType::query()->orderBy('name');

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool)$request->get('is_active'));
        }

        $data = $query->paginate($perPage);
        return VehicleTypeResource::collection($data)->response();
    }

    public function list()
    {
        return response()->json(['success' => true, 'data' => VehicleType::orderBy('name')->get()]);
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
        ]);
        // Ensure tenant_id is set from authenticated user
        if (auth()->check() && auth()->user()->tenant_id) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        $vehicleType = VehicleType::create($data);
        return new VehicleTypeResource($vehicleType);
    }

    public function show(VehicleType $vehicleType)
    {
        return new VehicleTypeResource($vehicleType);
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'nullable|integer',
            'price_per_km' => 'nullable|numeric',
            'ac_extra_price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $vehicleType->update($data);
        return new VehicleTypeResource($vehicleType);
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return response()->json(['success' => true]);
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $perPage = $request->get('per_page', 20);
        $query = VehicleType::query();
        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }
        $data = $query->orderBy('name')->paginate($perPage);
        return VehicleTypeResource::collection($data)->response();
    }
}
