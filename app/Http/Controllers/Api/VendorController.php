<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Vendor, VendorBill, Vehicle};
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
            // 'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
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
        $p = $q->latest()->paginate($per);
        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    }

    // PUT/PATCH /api/v1/vendors/{vendor}
    public function update(Request $request, Vendor $vendor)
    {
        $this->checkRole(['superadmin', 'admin']);

        $v = Validator::make($request->all(), [
            'vendor_name' => 'sometimes|required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            // 'contract_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'duty_type' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
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
        $vendor->load('vehicles');
        $bills = VendorBill::where('vendor_id', $vendor->id)->orderBy('billing_date', 'desc')->get();
        return response()->json(['success' => true, 'data' => ['vendor' => $vendor, 'assigned_vehicles' => $vendor->vehicles, 'billing_history' => $bills]]);
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
