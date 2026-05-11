<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Vendor, VendorBill, Vehicle, Staff};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, Validator};

class VendorController extends Controller
{
    // POST /api/v1/vendors
    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $v = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:0',
            'monthly_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $data['tenant_id'] = Auth::user()->tenant_id ?? null;
        $data['created_by'] = Auth::id();

        $vendor = Vendor::create($data);

        return response()->json(['success' => true, 'message' => 'Vendor created', 'data' => $vendor], 201);
    }

    // GET /api/v1/vendors
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);
        $q = Vendor::query();
        if ($request->search) {
            $s = $request->search;
            $q->where(function ($query) use ($s) {
                $query->where('vendor_name', 'like', "%{$s}%")
                    ->orWhere('contact_number', 'like', "%{$s}%")
                ;
            });
        }
        $per = $request->integer('per_page', 20);

        $p = $q->with(['vehicleTypeDetails'])
           ->latest()
           ->paginate($per);

        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    }

    public function toggleStatus($vendorId)
    {
        try {
            $vendor = Vendor::findOrFail($vendorId);

            // Toggle status
            $vendor->status = !$vendor->status;
            $vendor->save();

            return response()->json([
                'success' => true,
                'message' => 'Vendor status updated successfully',
                'data' => [
                    'vendor_id' => $vendor->id,
                    'status' => $vendor->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // PUT/PATCH /api/v1/vendors/{vendor}
    public function update(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin']);

        $v = Validator::make($request->all(), [
            'vendor_name' => 'sometimes|required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:0',
            'monthly_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $vendor->update($v->validated());

        return response()->json(['success' => true, 'message' => 'Vendor updated', 'data' => $vendor->fresh()]);
    }

    // GET /api/v1/vendors/{vendor}
    public function show(Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $vendor->load([
            'vehicles',
            'vehicleTypeDetails'
        ]);

        $vehicles = $vendor->vehicles->map(function ($vehicle) {

            $vehicle->type = $vehicle->vehicleTypeDetails->name ?? null;

            return $vehicle;
        });

        $bills = VendorBill::where('vendor_id', $vendor->id)->orderBy('billing_date', 'desc')->get();
        return response()->json(['success' => true, 'data' => [
            'vendor' => $vendor,
            'assigned_vehicles' => $vehicles,
            'assigned_drivers' => $vendor->drivers()->get(),
            'billing_history' => $bills
        ]]);
    }
    

    // GET /api/v1/vendors/{vendor}/available-drivers
    public function availableDrivers(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        // Use staff from the same tenant as the vendor. Skip global tenant scope so
        // we can explicitly filter by the vendor's tenant_id even if caller auth differs.
        // Some installations store driver role in `staff_type`, others use `work_shift`.
        // Match either to reliably find drivers across deployments.
        $q = Staff::withoutGlobalScopes()->available()
            ->where('tenant_id', $vendor->tenant_id)
            ->whereNull('deleted_at')
            ->where(function($qq) {
                $qq->where('staff_type', '4');
            });

        // exclude drivers already assigned to this vendor
        $q->whereNotIn('id', function ($sub) use ($vendor) {
            $sub->select('staff_id')->from('vendor_staff')->where('vendor_id', $vendor->id);
        });

        if ($request->search) {
            $s = $request->search;
            $q->where(function($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $per = $request->integer('per_page', 20);
        $p = $q->latest()->paginate($per);

        // Debug mode: return diagnostic info to help troubleshoot tenant/filters
        if ($request->boolean('debug')) {
            $tenantDriversCount = Staff::withoutGlobalScopes()->where('tenant_id', $vendor->tenant_id)
                ->where(function($qq) {
                    $qq->where('staff_type', 4)->orWhere('work_shift', 'driver');
                })->count();
            $assignedIds = \DB::table('vendor_staff')->where('vendor_id', $vendor->id)->pluck('staff_id')->toArray();
            $availableCount = $p->total();

            return response()->json([
                'success' => true,
                'data' => $p->items(),
                'meta' => ['total' => $availableCount],
                'diagnostic' => [
                    'vendor_id' => $vendor->id,
                    'vendor_tenant_id' => $vendor->tenant_id,
                    'drivers_in_tenant_count' => $tenantDriversCount,
                    'assigned_driver_ids' => $assignedIds,
                    'available_driver_count' => $availableCount,
                ],
            ]);
        }

        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total()]]);
    }

    // POST /api/v1/vendors/{vendor}/assign-drivers
    public function assignDrivers(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin']);

        $v = \Validator::make($request->all(), ['staff_ids' => 'required|array']);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $ids = $request->staff_ids;
        $tenant_id = Auth::user()->tenant_id ?? null;
        foreach ($ids as $staffId) {
            \DB::table('vendor_staff')->updateOrInsert(
                ['vendor_id' => $vendor->id, 'staff_id' => $staffId],
                ['tenant_id' => $tenant_id, 'assigned_by' => Auth::id(), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return response()->json(['success' => true, 'message' => 'Drivers assigned to vendor']);
    }

    // DELETE /api/v1/vendors/{vendor}/remove-driver/{staff}
    public function removeDriver(Vendor $vendor, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin']);
        \DB::table('vendor_staff')->where('vendor_id', $vendor->id)->where('staff_id', $staff->id)->delete();
        return response()->json(['success' => true, 'message' => 'Driver removed from vendor']);
    }

    // GET /api/v1/vendors/{vendor}/available-vehicles
    public function availableVehicles(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);
        $q = Vehicle::query()->where('is_active', true);
        // exclude assigned to this vendor
        $q->whereNotIn('id', function ($sub) use ($vendor) {
            $sub->select('vehicle_id')->from('vendor_vehicle')->where('vendor_id', $vendor->id);
        });
        if ($request->search) {
            $s = $request->search;
            $q->where('registration_number', 'like', "%{$s}%")->orWhere('type', 'like', "%{$s}%");
        }
        $per = $request->integer('per_page', 20);
        $p = $q->latest()->paginate($per);
        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total()]]);
    }

    // POST /api/v1/vendors/{vendor}/assign-vehicles
    public function assignVehicles(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin']);
        $v = Validator::make($request->all(), ['vehicle_ids' => 'required|array']);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        $ids = $request->vehicle_ids;
        $tenant_id = Auth::user()->tenant_id ?? null;
        foreach ($ids as $vehicleId) {
            \DB::table('vendor_vehicle')->updateOrInsert(
                ['vendor_id' => $vendor->id, 'vehicle_id' => $vehicleId],
                ['tenant_id' => $tenant_id, 'assigned_by' => Auth::id(), 'updated_at' => now(), 'created_at' => now()]
            );
        }
        return response()->json(['success' => true, 'message' => 'Vehicles assigned']);
    }

    // DELETE /api/v1/vendors/{vendor}/remove-vehicle/{vehicle}
    public function removeVehicle(Vendor $vendor, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin']);
        \DB::table('vendor_vehicle')->where('vendor_id', $vendor->id)->where('vehicle_id', $vehicle->id)->delete();
        return response()->json(['success' => true, 'message' => 'Vehicle removed from vendor']);
    }

    // POST /api/v1/vendors/{vendor}/bills
    public function addBill(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);
        $v = Validator::make($request->all(), [
            'invoice_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_date' => 'required|date',
            'status' => 'nullable|in:pending,paid',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $data['tenant_id'] = Auth::user()->tenant_id ?? null;
        $data['vendor_id'] = $vendor->id;
        $data['created_by'] = Auth::id();

        if ($request->hasFile('file')) {
            $dir = "tenants/{$data['tenant_id']}/vendors/{$vendor->id}/bills";
            $path = $request->file('file')->store($dir, 'public');
            $data['file_path'] = $path;
        }

        $bill = VendorBill::create($data);
        return response()->json(['success' => true, 'message' => 'Bill added', 'data' => $bill], 201);
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) abort(403, 'You do not have permission');
    }
}
