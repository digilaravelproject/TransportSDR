<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Vehicle, VehicleFuelLog, VehicleMaintenanceLog, VehicleDocument, VehicleSparePart, VehicleLedger};
use App\Http\Resources\VehicleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, File, Storage};
use Carbon\Carbon;

class VehicleController extends Controller
{
    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

            $vehicles = Vehicle::withCount(['trips'])
                ->when($request->type,         fn($q, $v) => $q->where('type', $v))
                ->when($request->is_available, fn($q, $v) => $q->where('is_available', (bool)$v))
                ->when($request->search,       fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('registration_number', 'like', "%{$v}%")
                        ->orWhere('type',  'like', "%{$v}%")
                        ->orWhere('make',  'like', "%{$v}%")
                        ->orWhere('model', 'like', "%{$v}%");
                }))
                ->latest()
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'data'    => VehicleResource::collection($vehicles),
                'meta'    => [
                    'total'        => $vehicles->total(),
                    'current_page' => $vehicles->currentPage(),
                    'last_page'    => $vehicles->lastPage(),
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view vehicles.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicles. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vehicles NOT assigned to given route
     */
    public function availableVehicles(Request $request, $route_id)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
            // Date to check availability for (defaults to today)
            $checkDate = $request->date ? Carbon::parse($request->date)->toDateString() : now()->toDateString();

            $vehicles = Vehicle::withCount(['trips'])

                // Exclude vehicles already assigned to this route (pivot)
                ->whereNotIn('id', function ($query) use ($route_id) {
                    $query->select('vehicle_id')
                        ->from('route_vehicle')
                        ->where('route_id', $route_id);
                })

                // Exclude vehicles that have a Trip overlapping the check date
                ->whereNotIn('id', function ($query) use ($checkDate) {
                    $query->select('vehicle_id')
                        ->from('trips')
                        ->where(function ($q) use ($checkDate) {
                            $q->whereDate('trip_date', '<=', $checkDate)
                              ->where(function ($q2) use ($checkDate) {
                                  $q2->whereDate('return_date', '>=', $checkDate)
                                     ->orWhereNull('return_date');
                              });
                        })
                        ->where('status', '!=', 'cancelled');
                })

                // Optional filters (same as index)
                ->when($request->type, fn($q, $v) => $q->where('type', $v))
                ->when($request->is_available, fn($q, $v) => $q->where('is_available', (bool)$v))
                ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('registration_number', 'like', "%{$v}%")
                        ->orWhere('type', 'like', "%{$v}%")
                        ->orWhere('make', 'like', "%{$v}%")
                        ->orWhere('model', 'like', "%{$v}%");
                }))

                ->latest()
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'message' => 'Available vehicles retrieved successfully',
                'data'    => VehicleResource::collection($vehicles),
                'meta'    => [
                    'total'        => $vehicles->total(),
                    'current_page' => $vehicles->currentPage(),
                    'last_page'    => $vehicles->lastPage(),
                ],
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view vehicles.',
                'error'   => $e->getMessage(),
            ], 403);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available vehicles.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/vehicles
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $this->checkRole(['superadmin', 'admin']);

            $data = $request->validate([
                'registration_number' => 'required|string|max:20|unique:vehicles,registration_number',
                'type'                => 'required|string|max:100',
                'seating_capacity'    => 'required|integer|min:1',
                'make'                => 'nullable|string|max:100',
                'model'               => 'nullable|string|max:100',
                'fuel_type'           => 'nullable|in:diesel,petrol,cng,electric',
                'current_km'          => 'nullable|numeric|min:0',
            ], [
                'registration_number.required' => 'Registration number is required.',
                'registration_number.unique'   => 'This registration number already exists.',
                'type.required'                => 'Vehicle type is required.',
                'seating_capacity.required'    => 'Seating capacity is required.',
                'seating_capacity.integer'     => 'Seating capacity must be a number.',
            ]);

            $vehicle = Vehicle::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle added successfully.',
                'data'    => new VehicleResource($vehicle),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add a vehicle.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while adding the vehicle.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles/{id}
    // ─────────────────────────────────────────────────
    public function show(Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

            $vehicle->load(['trips' => fn($q) => $q->latest()->take(5)]);

            // Latest fuel log
            $latestFuel = VehicleFuelLog::where('vehicle_id', $vehicle->id)->latest()->first();

            // Expiring documents
            $expiringDocs = VehicleDocument::where('vehicle_id', $vehicle->id)
                ->where('expiry_date', '>=', now())
                ->where('expiry_date', '<=', now()->addDays(60))
                ->get();

            // Low stock parts
            $lowStockParts = VehicleSparePart::where('vehicle_id', $vehicle->id)
                ->whereColumn('quantity_in_stock', '<=', 'minimum_stock_alert')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'vehicle'        => new VehicleResource($vehicle),
                    'latest_fuel'    => $latestFuel,
                    'expiring_docs'  => $expiringDocs,
                    'low_stock'      => $lowStockParts,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this vehicle.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicle details. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PUT /api/v1/vehicles/{id}
    // ─────────────────────────────────────────────────
    public function update(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin']);

            $data = $request->validate([
                'registration_number' => 'sometimes|string|max:20|unique:vehicles,registration_number,' . $vehicle->id,
                'type'                => 'sometimes|string|max:100',
                'seating_capacity'    => 'sometimes|integer|min:1',
                'make'                => 'nullable|string|max:100',
                'model'               => 'nullable|string|max:100',
                'fuel_type'           => 'nullable|in:diesel,petrol,cng,electric',
                'current_km'          => 'nullable|numeric|min:0',
                'is_available'        => 'boolean',
                'is_active'           => 'boolean',
            ]);

            $vehicle->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully.',
                'data'    => new VehicleResource($vehicle->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this vehicle.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/vehicles/{id}
    // ─────────────────────────────────────────────────
    public function destroy(Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin']);

            if (!$vehicle->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle is currently on a trip. Cannot delete.',
                ], 422);
            }

            $vehicle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully.',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this vehicle.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vehicle. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/vehicles/{id}/fuel
    // Diesel/Petrol/AdBlue entry
    // ─────────────────────────────────────────────────
    public function addFuel(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

            $data = $request->validate([
                'fuel_type'       => 'required|in:diesel,petrol,cng,adblue,electric',
                'quantity_liters' => 'required|numeric|min:0.1',
                'price_per_liter' => 'required|numeric|min:0',
                'km_at_fill'      => 'required|numeric|min:0',
                'fuel_station'    => 'nullable|string|max:255',
                'payment_mode'    => 'nullable|in:cash,card,upi,account',
                'bill_number'     => 'nullable|string|max:100',
                'filled_on'       => 'required|date',
                'notes'           => 'nullable|string',
            ], [
                'quantity_liters.required' => 'Fuel quantity is required.',
                'price_per_liter.required' => 'Price per liter is required.',
                'km_at_fill.required'      => 'Current KM reading is required.',
            ]);

            $log = VehicleFuelLog::create(array_merge($data, [
                'vehicle_id' => $vehicle->id,
            ]));

            // Update vehicle current KM
            if ($data['km_at_fill'] > $vehicle->current_km) {
                $vehicle->update(['current_km' => $data['km_at_fill']]);
            }

            return response()->json([
                'success' => true,
                'message' => "Fuel log added. Efficiency: {$log->fuel_efficiency} km/L",
                'data'    => $log,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add a fuel log.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to add fuel log. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/vehicles/{id}/maintenance
    // Repair, service, lubricant, spare parts
    // ─────────────────────────────────────────────────
    public function addMaintenance(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator']);

            $data = $request->validate([
                'maintenance_type'  => 'required|in:repair,service,lubricant,spare_part,tyre,battery,other',
                'title'             => 'required|string|max:255',
                'description'       => 'nullable|string',
                'labour_cost'       => 'nullable|numeric|min:0',
                'parts_cost'        => 'nullable|numeric|min:0',
                'km_at_service'     => 'nullable|numeric|min:0',
                'next_service_km'   => 'nullable|numeric|min:0',
                'next_service_date' => 'nullable|date',
                'vendor_name'       => 'nullable|string|max:255',
                'vendor_contact'    => 'nullable|string|max:15',
                'bill_number'       => 'nullable|string|max:100',
                'status'            => 'nullable|in:pending,in_progress,completed',
                'service_date'      => 'required|date',
                'notes'             => 'nullable|string',
            ], [
                'maintenance_type.required' => 'Maintenance type is required.',
                'title.required'            => 'Title is required.',
                'service_date.required'     => 'Service date is required.',
            ]);

            $log = VehicleMaintenanceLog::create(array_merge($data, [
                'vehicle_id' => $vehicle->id,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Maintenance log added successfully.',
                'data'    => $log,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add a maintenance log.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to add maintenance log. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/vehicles/{id}/document
    // RC, Insurance, Pollution, Permit etc.
    // ─────────────────────────────────────────────────
    public function addDocument(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator']);

            $data = $request->validate([
                'document_type'     => 'required|in:rc,insurance,pollution,permit,fitness,tax,other',
                'document_number'   => 'nullable|string|max:100',
                'issue_date'        => 'nullable|date',
                'expiry_date'       => 'required|date|after:today',
                'alert_before_days' => 'nullable|integer|min:1|max:365',
                'notes'             => 'nullable|string',
                'document_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ], [
                'document_type.required'  => 'Document type is required.',
                'expiry_date.required'    => 'Expiry date is required.',
                'expiry_date.after'       => 'Expiry date must be a future date.',
                'document_file.max'       => 'File size must not exceed 5MB.',
            ]);

            // File upload
            $documentPath = null;
            if ($request->hasFile('document_file')) {
                $file         = $request->file('document_file');
                $fileName     = "doc-{$vehicle->id}-" . time() . '.' . $file->extension();
                $directory    = "tenants/{$vehicle->tenant_id}/vehicle-docs/{$vehicle->id}";
                $documentPath = $file->storeAs($directory, $fileName, 'public');
            }

            $document = VehicleDocument::create(array_merge(
                $data,
                [
                    'vehicle_id'    => $vehicle->id,
                    'document_path' => $documentPath,
                ]
            ));

            return response()->json([
                'success'  => true,
                'message'  => 'Document added successfully.',
                'data'     => $document,
                'file_url' => $documentPath ? asset("storage/{$documentPath}") : null,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add a document.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/vehicles/{id}/spare-part
    // Spare part add/update
    // ─────────────────────────────────────────────────
    public function addSparePart(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator']);

            $data = $request->validate([
                'part_name'               => 'required|string|max:255',
                'part_number'             => 'nullable|string|max:100',
                'category'                => 'nullable|string|max:100',
                'quantity_in_stock'       => 'required|integer|min:0',
                'minimum_stock_alert'     => 'nullable|integer|min:0',
                'unit'                    => 'nullable|string|max:50',
                'unit_price'              => 'nullable|numeric|min:0',
                'condition'               => 'nullable|in:good,fair,needs_replacement',
                'last_replaced_on'        => 'nullable|date',
                'km_at_replacement'       => 'nullable|numeric|min:0',
                'replacement_interval_km' => 'nullable|numeric|min:0',
                'vendor_name'             => 'nullable|string|max:255',
                'notes'                   => 'nullable|string',
            ], [
                'part_name.required'         => 'Part name is required.',
                'quantity_in_stock.required' => 'Quantity in stock is required.',
            ]);

            $part = VehicleSparePart::create(array_merge($data, [
                'vehicle_id' => $vehicle->id,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Spare part added successfully.',
                'data'    => $part,
                'alert'   => $part->isLowStock() ? 'Warning: Stock is below minimum level.' : null,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add a spare part.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to add spare part. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles/{id}/report
    // Full vehicle report with ledger
    // ─────────────────────────────────────────────────
    public function report(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'accountant']);

            $from = $request->from ?? now()->startOfMonth()->toDateString();
            $to   = $request->to   ?? now()->toDateString();

            // Fuel summary
            $fuelSummary = VehicleFuelLog::where('vehicle_id', $vehicle->id)
                ->whereBetween('filled_on', [$from, $to])
                ->selectRaw('
                    COUNT(*) as total_fills,
                    SUM(quantity_liters) as total_liters,
                    SUM(total_cost) as total_fuel_cost,
                    AVG(fuel_efficiency) as avg_efficiency
                ')
                ->first();

            // Maintenance summary
            $maintenanceSummary = VehicleMaintenanceLog::where('vehicle_id', $vehicle->id)
                ->whereBetween('service_date', [$from, $to])
                ->selectRaw('
                    COUNT(*) as total_services,
                    SUM(total_cost) as total_maintenance_cost,
                    SUM(labour_cost) as total_labour,
                    SUM(parts_cost) as total_parts
                ')
                ->first();

            // Ledger summary
            $income  = VehicleLedger::where('vehicle_id', $vehicle->id)
                ->where('entry_type', 'income')
                ->whereBetween('entry_date', [$from, $to])
                ->sum('amount');

            $expense = VehicleLedger::where('vehicle_id', $vehicle->id)
                ->where('entry_type', 'expense')
                ->whereBetween('entry_date', [$from, $to])
                ->sum('amount');

            // Document expiry status
            $documents = VehicleDocument::where('vehicle_id', $vehicle->id)
                ->orderBy('expiry_date')
                ->get()
                ->map(fn($d) => [
                    'type'           => $d->document_type,
                    'number'         => $d->document_number,
                    'expiry_date'    => $d->expiry_date->format('d-m-Y'),
                    'days_remaining' => $d->daysUntilExpiry(),
                    'status'         => $d->is_expired ? 'expired' : ($d->isExpiringSoon() ? 'expiring_soon' : 'valid'),
                ]);

            // Fuel logs
            $fuelLogs = VehicleFuelLog::where('vehicle_id', $vehicle->id)
                ->whereBetween('filled_on', [$from, $to])
                ->latest()
                ->get();

            // Maintenance logs
            $maintenanceLogs = VehicleMaintenanceLog::where('vehicle_id', $vehicle->id)
                ->whereBetween('service_date', [$from, $to])
                ->latest()
                ->get();

            // Spare parts health
            $spareParts = VehicleSparePart::where('vehicle_id', $vehicle->id)->get()
                ->map(fn($p) => [
                    'part_name'         => $p->part_name,
                    'quantity_in_stock' => $p->quantity_in_stock,
                    'condition'         => $p->condition,
                    'is_low_stock'      => $p->isLowStock(),
                    'is_available'      => $p->is_available,
                ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'vehicle'     => [
                        'id'                  => $vehicle->id,
                        'registration_number' => $vehicle->registration_number,
                        'type'                => $vehicle->type,
                        'make'                => $vehicle->make,
                        'model'               => $vehicle->model,
                        'current_km'          => $vehicle->current_km,
                    ],
                    'period'      => ['from' => $from, 'to' => $to],
                    'fuel'        => [
                        'total_fills'    => $fuelSummary->total_fills ?? 0,
                        'total_liters'   => round($fuelSummary->total_liters ?? 0, 2),
                        'total_cost'     => round($fuelSummary->total_fuel_cost ?? 0, 2),
                        'avg_efficiency' => round($fuelSummary->avg_efficiency ?? 0, 2) . ' km/L',
                        'logs'           => $fuelLogs,
                    ],
                    'maintenance' => [
                        'total_services' => $maintenanceSummary->total_services ?? 0,
                        'total_cost'     => round($maintenanceSummary->total_maintenance_cost ?? 0, 2),
                        'total_labour'   => round($maintenanceSummary->total_labour ?? 0, 2),
                        'total_parts'    => round($maintenanceSummary->total_parts ?? 0, 2),
                        'logs'           => $maintenanceLogs,
                    ],
                    'ledger'      => [
                        'total_income'    => round($income, 2),
                        'total_expense'   => round($expense, 2),
                        'net_profit_loss' => round($income - $expense, 2),
                    ],
                    'documents'   => $documents,
                    'spare_parts' => $spareParts,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this report.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate vehicle report. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles/{id}/history
    // Fuel + Maintenance combined history
    // ─────────────────────────────────────────────────
    public function history(Request $request, Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

            $fuelLogs = VehicleFuelLog::where('vehicle_id', $vehicle->id)
                ->when($request->from, fn($q, $v) => $q->whereDate('filled_on', '>=', $v))
                ->when($request->to,   fn($q, $v) => $q->whereDate('filled_on', '<=', $v))
                ->latest()
                ->get()
                ->map(fn($l) => array_merge($l->toArray(), ['log_type' => 'fuel']));

            $maintenanceLogs = VehicleMaintenanceLog::where('vehicle_id', $vehicle->id)
                ->when($request->from, fn($q, $v) => $q->whereDate('service_date', '>=', $v))
                ->when($request->to,   fn($q, $v) => $q->whereDate('service_date', '<=', $v))
                ->latest()
                ->get()
                ->map(fn($l) => array_merge($l->toArray(), ['log_type' => 'maintenance']));

            $ledger = VehicleLedger::where('vehicle_id', $vehicle->id)
                ->when($request->from, fn($q, $v) => $q->whereDate('entry_date', '>=', $v))
                ->when($request->to,   fn($q, $v) => $q->whereDate('entry_date', '<=', $v))
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data'    => [
                    'fuel_logs'        => $fuelLogs,
                    'maintenance_logs' => $maintenanceLogs,
                    'ledger'           => $ledger,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view vehicle history.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicle history. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles/{id}/gps
    // GPS location (third party API placeholder)
    // ─────────────────────────────────────────────────
    public function gpsLocation(Vehicle $vehicle)
    {
        try {
            $this->checkRole(['superadmin', 'admin', 'operator', 'driver']);

            // TODO: Replace with actual GPS API (e.g., Vamosys, TrackSo, etc.)
            // $response = Http::get("https://gps-api.example.com/location", [
            //     'vehicle_number' => $vehicle->registration_number,
            //     'api_key'        => config('services.gps.key'),
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'GPS integration ready. Connect your GPS provider API.',
                'data'    => [
                    'vehicle_id'          => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'gps_status'          => 'api_not_connected',
                    'note'                => 'Integrate GPS provider like Vamosys, TrackSo, Uffizio.',
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view GPS location.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
                'error'   => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch GPS location. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/vehicles/documents/expiring
    // All expiring documents across all vehicles
    // ─────────────────────────────────────────────────
    public function expiringDocuments(Request $request)
    {

        try {
            $this->checkRole(['superadmin', 'admin', 'operator']);

            $days = $request->integer('days', 60);

            $documents = VehicleDocument::with('vehicle')
                ->where('expiry_date', '>=', now())
                ->where('expiry_date', '<=', now()->addDays($days))
                ->where('is_expired', false)
                ->orderBy('expiry_date')
                ->get()
                ->map(fn($d) => [
                    'vehicle'         => $d->vehicle?->registration_number,
                    'vehicle_type'    => $d->vehicle?->type,
                    'document_type'   => $d->document_type,
                    'document_number' => $d->document_number,
                    'expiry_date'     => $d->expiry_date->format('d-m-Y'),
                    'days_remaining'  => $d->daysUntilExpiry(),
                    'file_url'        => $d->document_path ? asset("storage/{$d->document_path}") : null,
                ]);

            $expired = VehicleDocument::with('vehicle')
                ->where('is_expired', true)
                ->orderBy('expiry_date', 'desc')
                ->get()
                ->map(fn($d) => [
                    'vehicle'         => $d->vehicle?->registration_number,
                    'document_type'   => $d->document_type,
                    'document_number' => $d->document_number,
                    'expiry_date'     => $d->expiry_date->format('d-m-Y'),
                    'expired_since'   => $d->expiry_date->diffForHumans(),
                ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'expiring_soon' => $documents,
                    'expired'       => $expired,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view expiring documents.',
                'error'   => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expiring documents. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Role check helper
    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
