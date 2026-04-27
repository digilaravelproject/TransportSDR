<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Vehicle, VehicleDocument};
use App\Models\VehicleActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};
use Illuminate\Support\Facades\Validator;

class VehicleActivityController extends Controller
{
    // POST /api/v1/vehicles/{vehicle}/activity/fuel
    public function storeFuel(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

        $v = Validator::make($request->all(), [
            'activity_date' => 'required|date',
            'quantity'      => 'required|numeric|min:0',
            'price_per_unit'=> 'required|numeric|min:0',
            'amount'        => 'required|numeric|min:0',
            'station_name'  => 'nullable|string|max:255',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $data['activity_type'] = 'fuel';
        $data['tenant_id'] = Auth::user()->tenant_id ?? null;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = Auth::id();

        // receipt file
        if ($request->hasFile('receipt')) {
            $dir = "tenants/{$data['tenant_id']}/vehicles/{$vehicle->id}/activities";
            $path = $request->file('receipt')->store($dir, 'public');
            $data['receipt_path'] = $path;
        }

        $activity = VehicleActivity::create($data);

        $id = $activity->id;

        return response()->json(['success' => true, 'message' => 'Fuel entry created.', 'data' => ['id' => $id]] , 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/fuel
    public function fuelHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'fuel')
            ->orderBy('activity_date', 'desc');

        $p = $q->paginate($perPage);

        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    }

    // POST /api/v1/vehicles/{vehicle}/activity/service
    public function storeService(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $v = Validator::make($request->all(), [
            'activity_date' => 'required|date',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'amount_paid'   => 'nullable|numeric|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'km_reading'    => 'nullable|numeric|min:0',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $data['activity_type'] = 'service';
        $data['tenant_id'] = Auth::user()->tenant_id ?? null;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = Auth::id();

        if ($request->hasFile('receipt')) {
            $dir = "tenants/{$data['tenant_id']}/vehicles/{$vehicle->id}/activities";
            $path = $request->file('receipt')->store($dir, 'public');
            $data['receipt_path'] = $path;
        }

        $service = VehicleActivity::create($data);

        $id = $service->id;

        return response()->json(['success' => true, 'message' => 'Service entry created.', 'data' => ['id' => $id]] , 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/service
    public function serviceHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'service')
            ->orderBy('activity_date', 'desc');

        $p = $q->paginate($perPage);

        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    }

    // POST /api/v1/vehicles/{vehicle}/activity/repair
    public function storeRepair(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $v = Validator::make($request->all(), [
            'activity_date' => 'required|date',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'amount_paid'   => 'nullable|numeric|min:0',
            'garage_name'   => 'nullable|string|max:255',
            'km_reading'    => 'nullable|numeric|min:0',
            'receipt'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'         => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $data['activity_type'] = 'repair';
        $data['tenant_id'] = Auth::user()->tenant_id ?? null;
        $data['vehicle_id'] = $vehicle->id;
        $data['created_by'] = Auth::id();

        if ($request->hasFile('receipt')) {
            $dir = "tenants/{$data['tenant_id']}/vehicles/{$vehicle->id}/activities";
            $path = $request->file('receipt')->store($dir, 'public');
            $data['receipt_path'] = $path;
        }

        $repair = VehicleActivity::create($data);

        $id = $repair->id;

        return response()->json(['success' => true, 'message' => 'Repair entry created.', 'data' => ['id' => $id]] , 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/repair
    public function repairHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'repair')
            ->orderBy('activity_date', 'desc');

        $p = $q->paginate($perPage);

        return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    }

    // GET /api/v1/vehicles/{vehicle}/documents
    public function documents(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $docs = collect();

        // RC Document
        if ($vehicle->rc_number || $vehicle->rc_expiry || $vehicle->rc_file) {
            $docs->push([
                'id' => $vehicle->id,
                'type' => 'RC',
                'number' => $vehicle->rc_number,
                'expiry_date' => $vehicle->rc_expiry
                    ? \Carbon\Carbon::parse($vehicle->rc_expiry)->format('d-m-Y')
                    : null,
                'file_url' => $vehicle->rc_file
                    ? asset("storage/{$vehicle->rc_file}")
                    : null,
            ]);
        }

        // Insurance Document
        if ($vehicle->insurance_number || $vehicle->insurance_expiry || $vehicle->insurance_file) {
            $docs->push([
                'id' => $vehicle->id,
                'type' => 'Insurance',
                'number' => $vehicle->insurance_number,
                'expiry_date' => $vehicle->insurance_expiry
                    ? \Carbon\Carbon::parse($vehicle->insurance_expiry)->format('d-m-Y')
                    : null,
                'file_url' => $vehicle->insurance_file
                    ? asset("storage/{$vehicle->insurance_file}")
                    : null,
            ]);
        }

        // Permit Document
        if ($vehicle->permit_number || $vehicle->permit_expiry || $vehicle->permit_file) {
            $docs->push([
                'id' => $vehicle->id,
                'type' => 'Permit',
                'number' => $vehicle->permit_number,
                'expiry_date' => $vehicle->permit_expiry
                    ? \Carbon\Carbon::parse($vehicle->permit_expiry)->format('d-m-Y')
                    : null,
                'file_url' => $vehicle->permit_file
                    ? asset("storage/{$vehicle->permit_file}")
                    : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $docs->values()
        ]);
    }

    // GET /api/v1/vehicles/{vehicle}/timeline
    public function timeline(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        /*
        |--------------------------------------------------------------------------
        | Activities using Model
        |--------------------------------------------------------------------------
        */

        $activities = VehicleActivity::where('vehicle_id', $vehicle->id)
            ->select([
                'id',
                'activity_type',
                'title',
                'activity_date',
                'amount',
                'quantity',
                'receipt_path',
                'created_at'
            ])
            ->orderBy('activity_date', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'activity_type' => $a->activity_type,
                    'title' => $a->title,
                    'activity_date' => $a->activity_date,
                    'amount' => $a->amount,
                    'quantity' => $a->quantity,
                    'receipt_path' => $a->receipt_path,
                    'created_at' => $a->created_at,
                    'kind' => 'activity',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Documents from vehicles table
        |--------------------------------------------------------------------------
        */

        $docs = collect();

        $documentMap = [
            'rc' => 'RC',
            'insurance' => 'Insurance',
            'permit' => 'Permit',
        ];

        foreach ($documentMap as $prefix => $label) {

            $number = $vehicle->{$prefix . '_number'};
            $expiry = $vehicle->{$prefix . '_expiry'};
            $file   = $vehicle->{$prefix . '_file'};

            if ($number || $expiry || $file) {
                $docs->push([
                    'id' => $vehicle->id,
                    'activity_type' => 'document',
                    'title' => $label,
                    'activity_date' => $expiry,
                    'amount' => null,
                    'quantity' => null,
                    'receipt_path' => $file,
                    'created_at' => $vehicle->updated_at,
                    'kind' => 'document',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Combine + Sort
        |--------------------------------------------------------------------------
        */

        $combined = $activities
            ->concat($docs)
            ->sortByDesc(function ($i) {
                return $i['activity_date'] ?? $i['created_at'];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $combined
        ]);
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) abort(403, 'You do not have permission for this action.');
    }
}
