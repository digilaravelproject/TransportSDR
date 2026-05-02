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
            'price_per_unit' => 'required|numeric|min:0',
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

        return response()->json(['success' => true, 'message' => 'Fuel entry created.', 'data' => ['id' => $id]], 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/fuel
    // public function fuelHistory(Request $request, Vehicle $vehicle)
    // {
    //     $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
    //     $perPage = $request->integer('per_page', 20);

    //     $q = \DB::table('vehicle_activities')
    //         ->where('vehicle_id', $vehicle->id)
    //         ->where('activity_type', 'fuel')
    //         ->orderBy('activity_date', 'desc');

    //     $p = $q->paginate($perPage);

    //     return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    // }

    public function fuelHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        // --- Naye 3 keys ka calculation start ---

        // 1. Total Fuel Expense (Overall)
        $totalFuelExpense = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'fuel')
            ->sum('amount');

        // 2. Monthly Cost (Current Month)
        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;
        $monthlyCost = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'fuel')
            ->whereMonth('activity_date', $currentMonth)
            ->whereYear('activity_date', $currentYear)
            ->sum('amount');

        // 3. Avg/Ltr (Total Amount / Total Quantity)
        $totalQuantity = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'fuel')
            ->sum('quantity');

        // Division by zero se bachne ke liye condition
        $avgLtrPrice = $totalQuantity > 0 ? ($totalFuelExpense / $totalQuantity) : 0;

        // --- Naye 3 keys ka calculation end ---


        // Existing Data Query
        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'fuel')
            ->orderBy('activity_date', 'desc');

        $p = $q->paginate($perPage);

        $data = collect($p->items())->map(function ($item) {
            // Agar receipt_path me value hai, toh usko full URL se replace kar denge
            if (!empty($item->receipt_path)) {
                // Ek chota sa check taki agar pehle se 'http' ho toh do baar add na ho
                if (!str_starts_with($item->receipt_path, 'http')) {
                    $item->receipt_path = asset('storage/' . $item->receipt_path);
                }
            }

            return $item;
        });

        // Response me naye keys add kar diye gaye hain
        return response()->json([
            'success'            => true,
            'total_fuel_expense' => round($totalFuelExpense, 2),
            'monthly_cost'       => round($monthlyCost, 2),
            'avg_ltr_price'      => round($avgLtrPrice, 2),
            'data'               => $data,
            'meta'               => [
                'total'        => $p->total(),
                'current_page' => $p->currentPage()
            ]
        ]);
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

        return response()->json(['success' => true, 'message' => 'Service entry created.', 'data' => ['id' => $id]], 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/service
    // public function serviceHistory(Request $request, Vehicle $vehicle)
    // {
    //     $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
    //     $perPage = $request->integer('per_page', 20);

    //     $q = \DB::table('vehicle_activities')
    //         ->where('vehicle_id', $vehicle->id)
    //         ->where('activity_type', 'service')
    //         ->orderBy('activity_date', 'desc');

    //     $p = $q->paginate($perPage);

    //     return response()->json(['success' => true, 'data' => $p->items(), 'meta' => ['total' => $p->total(), 'current_page' => $p->currentPage()]]);
    // }

    public function serviceHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        // Base Query
        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'service');

        // 1. Date Range Search
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereBetween('activity_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $q->whereDate('activity_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $q->whereDate('activity_date', '<=', $request->end_date);
        }

        // 2. Status Search (Paid / Pending)
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                // Agar paid amount >= total amount hai, toh full paid
                $q->whereRaw('COALESCE(amount_paid, 0) >= amount');
            } elseif ($request->status === 'pending') {
                // Agar paid amount < total amount hai (ya null hai), toh pending/due
                $q->whereRaw('COALESCE(amount_paid, 0) < amount');
            }
        }

        // --- Calculations Start (Filtered query ke basis par) ---
        $totalsQuery = clone $q; // Pagination se pehle query clone karni padti hai totals ke liye
        $totalAmount = $totalsQuery->sum('amount');
        $totalPaid = $totalsQuery->sum('amount_paid');
        $totalDue = $totalAmount - $totalPaid;
        // --- Calculations End ---

        // Sorting & Pagination
        $q->orderBy('activity_date', 'desc');
        $p = $q->paginate($perPage);

        // Image/Receipt path formatting
        $data = collect($p->items())->map(function ($item) {
            if (!empty($item->receipt_path)) {
                if (!str_starts_with($item->receipt_path, 'http')) {
                    $item->receipt_path = asset('storage/' . $item->receipt_path);
                }
            }

            // Front-end ki sahuliyat ke liye individual row ka due amount bhi nikal dete hain
            $item->due_amount = number_format((float)$item->amount - (float)$item->amount_paid, 2, '.', '');

            return $item;
        });

        return response()->json([
            'success'      => true,
            'total_amount' => round($totalAmount, 2),
            'pay_amount'   => round($totalPaid, 2),
            'due_amount'   => round($totalDue, 2),
            'data'         => $data,
            'meta'         => [
                'total'        => $p->total(),
                'current_page' => $p->currentPage()
            ]
        ]);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/service/{service}
    public function showService(Request $request, Vehicle $vehicle, $serviceId)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $activity = VehicleActivity::where('vehicle_id', $vehicle->id)
            ->where('id', $serviceId)
            ->where('activity_type', 'service')
            ->firstOrFail();

        $activity->receipt_url = $activity->receipt_path ? asset("storage/{$activity->receipt_path}") : null;

        return response()->json(['success' => true, 'data' => $activity]);
    }

    // POST /api/v1/vehicles/{vehicle}/activity/service/{service}/payment
    public function payService(Request $request, Vehicle $vehicle, $serviceId)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant', 'driver']);

        $v = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $activity = VehicleActivity::where('vehicle_id', $vehicle->id)
            ->where('id', $serviceId)
            ->where('activity_type', 'service')
            ->firstOrFail();

        $data = $v->validated();
        $amount = (float) $data['amount'];

        // store receipt (if any) into meta.payments
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $tenantId = Auth::user()->tenant_id ?? null;
            $dir = "tenants/{$tenantId}/vehicles/{$vehicle->id}/activities/payments";
            $receiptPath = $request->file('receipt')->store($dir, 'public');
        }

        $meta = $activity->meta ?? [];
        $payments = $meta['payments'] ?? [];
        $payments[] = [
            'amount' => $amount,
            'paid_at' => $data['payment_date'] ?? now()->toDateTimeString(),
            'paid_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $receiptPath,
        ];
        $meta['payments'] = $payments;

        $activity->meta = $meta;
        $activity->amount_paid = (float) ($activity->amount_paid ?? 0) + $amount;
        $activity->save();

        $due = (float) $activity->amount - (float) $activity->amount_paid;

        return response()->json(['success' => true, 'message' => 'Payment recorded', 'data' => ['amount_paid' => $activity->amount_paid, 'due' => max(0, $due)]]);
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

        return response()->json(['success' => true, 'message' => 'Repair entry created.', 'data' => ['id' => $id]], 201);
    }

    // GET /api/v1/vehicles/{vehicle}/activity/repair
    public function repairHistory(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);
        $perPage = $request->integer('per_page', 20);

        // Base Query (activity_type = 'repair')
        $q = \DB::table('vehicle_activities')
            ->where('vehicle_id', $vehicle->id)
            ->where('activity_type', 'repair');

        // 1. Date Range Search (Supports start_date/end_date OR from/to)
        $startDate = $request->start_date ?? $request->from;
        $endDate   = $request->end_date ?? $request->to;

        if ($startDate && $endDate) {
            $q->whereBetween('activity_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $q->whereDate('activity_date', '>=', $startDate);
        } elseif ($endDate) {
            $q->whereDate('activity_date', '<=', $endDate);
        }

        // 2. Status Search (Paid / Pending)
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $q->whereRaw('COALESCE(amount_paid, 0) >= amount');
            } elseif ($request->status === 'pending') {
                $q->whereRaw('COALESCE(amount_paid, 0) < amount');
            }
        }

        // --- Calculations Start (Date Filter ke basis par) ---
        $totalsQuery = clone $q;
        $totalAmount = $totalsQuery->sum('amount');
        $totalPaid   = $totalsQuery->sum('amount_paid');
        $totalDue    = $totalAmount - $totalPaid;
        // --- Calculations End ---

        // Sorting & Pagination
        $q->orderBy('activity_date', 'desc');
        $p = $q->paginate($perPage);

        $data = collect($p->items())->map(function ($item) {
            // Receipt path me base URL add karne ka logic
            if (!empty($item->receipt_path)) {
                if (!str_starts_with($item->receipt_path, 'http')) {
                    $item->receipt_path = asset('storage/' . $item->receipt_path);
                }
            }

            // Individual row due amount
            $item->due_amount = number_format((float)$item->amount - (float)$item->amount_paid, 2, '.', '');

            return $item;
        });

        return response()->json([
            'success'      => true,
            'base_url'     => asset('storage'),
            'total_amount' => round($totalAmount, 2),
            'pay_amount'   => round($totalPaid, 2),
            'due_amount'   => round($totalDue, 2),
            'data'         => $data,
            'meta'         => [
                'total'        => $p->total(),
                'current_page' => $p->currentPage()
            ]
        ]);
    }

    // GET /api/v1/vehicles/{vehicle}/documents
    public function documents(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $docs = VehicleDocument::where('vehicle_id', $vehicle->id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'vehicle_id' => $doc->vehicle_id,
                        'type' => $doc->document_type,
                        'number' => $doc->document_number,
                        'issue_date' => $doc->issue_date
                            ? \Carbon\Carbon::parse($doc->issue_date)->format('d-m-Y')
                            : null,
                        'expiry_date' => $doc->expiry_date
                            ? \Carbon\Carbon::parse($doc->expiry_date)->format('d-m-Y')
                            : null,
                        'alert_before_days' => $doc->alert_before_days,
                        'notes' => $doc->notes,
                        'file_url' => $doc->document_path
                            ? asset("storage/{$doc->document_path}")
                            : null,
                        'created_at' => $doc->created_at
                            ? $doc->created_at->format('d-m-Y H:i:s')
                            : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $docs->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/v1/vehicles/{vehicle}/documents
    public function uploadDocument(Request $request, Vehicle $vehicle)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $v = Validator::make($request->all(), [
            'document_type' => 'required|string|max:100',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'alert_before_days' => 'nullable|integer|min:0',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data = $v->validated();
        $tenantId = Auth::user()->tenant_id ?? null;
        $dir = "tenants/{$tenantId}/vehicles/{$vehicle->id}/documents";
        $path = $request->file('file')->store($dir, 'public');

        $doc = VehicleDocument::create([
            'tenant_id' => $tenantId,
            'vehicle_id' => $vehicle->id,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'] ?? null,
            'document_path' => $path,
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'alert_before_days' => $data['alert_before_days'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Document uploaded', 'data' => $doc], 201);
    }

    // GET /api/v1/vehicles/{vehicle}/timeline
    // public function timeline(Request $request, Vehicle $vehicle)
    // {
    //     $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Activities using Model
    //     |--------------------------------------------------------------------------
    //     */

    //     $activities = VehicleActivity::where('vehicle_id', $vehicle->id)
    //         ->select([
    //             'id',
    //             'activity_type',
    //             'title',
    //             'activity_date',
    //             'amount',
    //             'quantity',
    //             'receipt_path',
    //             'created_at'
    //         ])
    //         ->orderBy('activity_date', 'desc')
    //         ->get()
    //         ->map(function ($a) {
    //             return [
    //                 'id' => $a->id,
    //                 'activity_type' => $a->activity_type,
    //                 'title' => $a->title,
    //                 'activity_date' => $a->activity_date,
    //                 'amount' => $a->amount,
    //                 'quantity' => $a->quantity,
    //                 'receipt_path' => $a->receipt_path,
    //                 'created_at' => $a->created_at,
    //                 'kind' => 'activity',
    //             ];
    //         });

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Documents from vehicles table
    //     |--------------------------------------------------------------------------
    //     */

    //     $docs = collect();

    //     $documentMap = [
    //         'rc' => 'RC',
    //         'insurance' => 'Insurance',
    //         'permit' => 'Permit',
    //     ];

    //     foreach ($documentMap as $prefix => $label) {

    //         $number = $vehicle->{$prefix . '_number'};
    //         $expiry = $vehicle->{$prefix . '_expiry'};
    //         $file   = $vehicle->{$prefix . '_file'};

    //         if ($number || $expiry || $file) {
    //             $docs->push([
    //                 'id' => $vehicle->id,
    //                 'activity_type' => 'document',
    //                 'title' => $label,
    //                 'activity_date' => $expiry,
    //                 'amount' => null,
    //                 'quantity' => null,
    //                 'receipt_path' => $file,
    //                 'created_at' => $vehicle->updated_at,
    //                 'kind' => 'document',
    //             ]);
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Combine + Sort
    //     |--------------------------------------------------------------------------
    //     */

    //     $combined = $activities
    //         ->concat($docs)
    //         ->sortByDesc(function ($i) {
    //             return $i['activity_date'] ?? $i['created_at'];
    //         })
    //         ->values();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $combined
    //     ]);
    // }

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
                // Receipt path me base URL add karne ka logic
                $receiptUrl = $a->receipt_path;
                if (!empty($receiptUrl) && !str_starts_with($receiptUrl, 'http')) {
                    $receiptUrl = asset('storage/' . $receiptUrl);
                }

                return [
                    'id' => $a->id,
                    'activity_type' => $a->activity_type,
                    'title' => $a->title,
                    'activity_date' => $a->activity_date,
                    'amount' => $a->amount,
                    'quantity' => $a->quantity,
                    'receipt_path' => $receiptUrl, // Updated receipt_path
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

            // Document file path me base URL add karne ka logic
            $receiptUrl = $file;
            if (!empty($receiptUrl) && !str_starts_with($receiptUrl, 'http')) {
                $receiptUrl = asset('storage/' . $receiptUrl);
            }

            if ($number || $expiry || $file) {
                $docs->push([
                    'id' => $vehicle->id,
                    'activity_type' => 'document',
                    'title' => $label,
                    'activity_date' => $expiry,
                    'amount' => null,
                    'quantity' => null,
                    'receipt_path' => $receiptUrl, // Updated receipt_path
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
