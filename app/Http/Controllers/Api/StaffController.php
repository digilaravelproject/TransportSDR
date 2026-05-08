<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Staff, StaffAttendance, StaffSalary, StaffAdvance, StaffDaLog, Trip};
use App\Http\Resources\StaffResource;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StaffController extends Controller
{
    public function __construct(private StaffService $service) {}

    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {

            $staff = Staff::with(['user', 'role', 'shift'])

                // Filter by role type/name
                ->when($request->type, function ($q, $v) {
                    $q->whereHas('role', function ($roleQuery) use ($v) {
                        $roleQuery->where('name', $v);
                    });
                })

                ->when($request->staff_type, fn($q, $v) =>
                    $q->where('staff_type', $v)
                )

                ->when($request->work_shift, fn($q, $v) =>
                    $q->where('work_shift', $v)
                )

                ->when($request->is_available, fn($q, $v) =>
                    $q->where('is_available', (bool)$v)
                )

                ->when($request->is_active, fn($q, $v) =>
                    $q->where('is_active', (bool)$v)
                )

                ->when($request->search, function ($q, $v) {
                    $q->where(function ($q) use ($v) {
                        $q->where('name', 'like', "%{$v}%")
                            ->orWhere('phone', 'like', "%{$v}%")
                            ->orWhere('email', 'like', "%{$v}%")
                            ->orWhere('staff_type', $v)
                            ->orWhereHas('role', function ($roleQuery) use ($v) {
                                $roleQuery->where('name', 'like', "%{$v}%");
                            });
                    });
                })

                ->latest()
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'data'    => StaffResource::collection($staff),
                'meta'    => [
                    'total'        => $staff->total(),
                    'current_page' => $staff->currentPage(),
                    'last_page'    => $staff->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching staff records.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        // Updated Validation rules for Form + Documents
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'phone'                  => 'required|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'required|exists:role_modules,id',
            'salary_type'            => 'nullable|in:monthly,daily',
            'work_shift'             => 'nullable|exists:shifts,id', // CHANGE KIYA HAI: Shift ki ID lega
            'assigned_vehicle'       => 'nullable|string|max:100',
            'basic_salary'           => 'nullable|numeric|min:0',
            'da_per_day'             => 'nullable|numeric|min:0',
            'hra'                    => 'nullable|numeric|min:0',
            'other_allowance'        => 'nullable|numeric|min:0',
            'address'                => 'nullable|string',
            'date_of_joining'        => 'nullable|date',
            'date_of_birth'          => 'nullable|date',
            'emergency_contact'      => 'nullable|string|max:15',
            'emergency_contact_name' => 'nullable|string|max:255',

            // License fields
            'license_number'         => 'nullable|string|max:100',
            'license_expiry'         => 'nullable|date',
            'license_type'           => 'nullable|string|max:50',

            // Bank details
            'bank_name'              => 'nullable|string|max:255',
            'bank_account'           => 'nullable|string|max:100',
            'bank_ifsc'              => 'nullable|string|max:30',

            // Document Fields
            'aadhar_number'          => 'nullable|string|max:50',
            'aadhar_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'pan_number'             => 'nullable|string|max:50',
            'pan_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dl_number'              => 'nullable|string|max:50',
            'dl_expiry'              => 'nullable|date',
            'dl_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'badge_number'           => 'nullable|string|max:50',
            'badge_expiry'           => 'nullable|date',
            'badge_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'passbook_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Ensure tenant_id is set
            $data['tenant_id'] = auth()->user()->tenant_id ?? null;

            // Naya service method call kiya hai jo staff aur files dono save karega

            $staff = $this->service->storeWithDocuments($data, $request);

            return response()->json([
                'success' => true,
                'message' => 'Staff and documents saved successfully.',
                'data'    => new StaffResource($staff),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the staff member.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $staff->load(['user', 'documents', 'role', 'shift']);

            $pendingAdvance = $staff->pendingAdvanceAmount();
            $pendingDA      = StaffDaLog::where('staff_id', $staff->id)
                ->where('status', 'pending')
                ->sum('da_amount');

            $recentTrips = Trip::where(function ($q) use ($staff) {
                $q->where('driver_id', $staff->id)
                    ->orWhere('helper_id', $staff->id);
            })->latest()->take(5)->get(['id', 'trip_number', 'trip_date', 'trip_route', 'status']);

            return response()->json([
                'success' => true,
                'data'    => [
                    'staff'           => new StaffResource($staff),
                    'pending_advance' => $pendingAdvance,
                    'pending_da'      => $pendingDA,
                    'recent_trips'    => $recentTrips,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching staff details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin']);

        // Updated Validation rules for Form + Documents
        $data = $request->validate([
            'name'                   => 'sometimes|string|max:255',
            'phone'                  => 'sometimes|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'sometimes|exists:role_modules,id',
            'salary_type'            => 'nullable|in:monthly,daily',
            'work_shift'             => 'nullable|exists:shifts,id', // CHANGE KIYA HAI: Shift ki ID lega
            'assigned_vehicle'       => 'nullable|string|max:100',
            'basic_salary'           => 'nullable|numeric|min:0',
            'da_per_day'             => 'nullable|numeric|min:0',
            'hra'                    => 'nullable|numeric|min:0',
            'other_allowance'        => 'nullable|numeric|min:0',
            'address'                => 'nullable|string',
            'date_of_joining'        => 'nullable|date',
            'date_of_birth'          => 'nullable|date',
            'emergency_contact'      => 'nullable|string|max:15',
            'emergency_contact_name' => 'nullable|string|max:255',
            'is_available'           => 'boolean',
            'is_active'              => 'boolean',

            // License fields
            'license_number'         => 'nullable|string|max:100',
            'license_expiry'         => 'nullable|date',
            'license_type'           => 'nullable|string|max:50',

            // Bank details
            'bank_name'              => 'nullable|string|max:255',
            'bank_account'           => 'nullable|string|max:100',
            'bank_ifsc'              => 'nullable|string|max:30',

            // Document Fields
            'aadhar_number'          => 'nullable|string|max:50',
            'aadhar_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'pan_number'             => 'nullable|string|max:50',
            'pan_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dl_number'              => 'nullable|string|max:50',
            'dl_expiry'              => 'nullable|date',
            'dl_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'badge_number'           => 'nullable|string|max:50',
            'badge_expiry'           => 'nullable|date',
            'badge_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'passbook_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            // Naya service method: updateWithDocuments
            $staff = $this->service->updateWithDocuments($staff, $data, $request);

            return response()->json([
                'success' => true,
                'message' => 'Staff and documents updated successfully.',
                'data'    => new StaffResource($staff),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the staff member.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/staff/search
     * Proxy to index for explicit search route (keeps route-friendly API)
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/staff/{id}
    // ─────────────────────────────────────────────────
    public function destroy(Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin']);

        if (!$staff->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Staff is currently on a trip. Cannot delete.',
            ], 422);
        }

        try {
            $staff->delete();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the staff member.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/staff/{id}/advance
    // Give advance to staff
    // ─────────────────────────────────────────────────
    public function giveAdvance(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'amount'          => 'required|numeric|min:1',
            'advance_date'    => 'required|date',
            'reason'          => 'nullable|string|max:255',
            'payment_mode'    => 'nullable|in:cash,bank,upi',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ], [
            'amount.required'       => 'Advance amount is required.',
            'advance_date.required' => 'Advance date is required.',
        ]);

        try {
            $advance = StaffAdvance::create(array_merge($data, [
                'staff_id' => $staff->id,
                'tenant_id' => auth()->user()->tenant_id ?? null,
                'created_by' => auth()->id(),
            ]));

            return response()->json([
                'success' => true,
                'message' => "Advance of ₹{$advance->amount} recorded for {$staff->name}.",
                'data'    => $advance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing the advance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/advances
    // Advance list
    // ─────────────────────────────────────────────────
    public function advanceList(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $query = StaffAdvance::where('staff_id', $staff->id);
            if ($request->has('is_deducted')) $query->where('is_deducted', (bool)$request->is_deducted);

            $advances = $query->latest()->paginate($request->integer('per_page', 20));

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_advance' => (float) StaffAdvance::where('staff_id', $staff->id)->sum('amount'),
                    'pending_amount' => $staff->pendingAdvanceAmount(),
                    'advances'       => $advances,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching advance records.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/trips
    // Trip history of staff
    // ─────────────────────────────────────────────────
    public function tripHistory(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $trips = Trip::where(function ($q) use ($staff) {
                $q->where('driver_id', $staff->id)
                    ->orWhere('helper_id', $staff->id);
            })
                ->when($request->from, fn($q, $v) => $q->whereDate('trip_date', '>=', $v))
                ->when($request->to,   fn($q, $v) => $q->whereDate('trip_date', '<=', $v))
                ->with(['customer', 'vehicle'])
                ->latest('trip_date')
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data'    => $trips,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching trip history.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/performance
    // All staff performance summary
    // ─────────────────────────────────────────────────
    public function performance(Request $request)
    {

        $this->checkRole(['superadmin', 'admin']);

        try {
            // Return aggregated performance for all staff (basic summary)
            $month = $request->month ?? now()->format('m');
            $year  = $request->year  ?? now()->format('Y');

            $list = Staff::withCount([
                'driverTrips as driver_trips_count' => fn($q) => $q->whereMonth('trip_date', $month)->whereYear('trip_date', $year),
                'helperTrips as helper_trips_count' => fn($q) => $q->whereMonth('trip_date', $month)->whereYear('trip_date', $year),
            ])->get()->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'total_trips' => $s->driver_trips_count + $s->helper_trips_count,
                'is_available' => $s->is_available,
            ]);

            return response()->json(['success' => true, 'data' => ['period' => ['month' => $month, 'year' => $year], 'performance' => $list]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred while fetching performance data.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getPerformance(Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $metric = \App\Models\StaffPerformanceMetric::where('staff_id', $staff->id)->latest()->first();

            // Monthly trips (last 6 months)
            $months = [];
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $label = $dt->format('M');
                $count = Trip::where(function($q) use ($staff){ $q->where('driver_id', $staff->id)->orWhere('helper_id', $staff->id); })
                    ->whereMonth('trip_date', $dt->month)
                    ->whereYear('trip_date', $dt->year)
                    ->count();
                $months[] = ['month' => $label, 'trips' => $count];
            }

            $recentFeedback = Trip::where(function($q) use ($staff){ $q->where('driver_id', $staff->id)->orWhere('helper_id', $staff->id); })
                ->whereNotNull('notes')
                ->latest('trip_date')
                ->take(5)
                ->get(['trip_number', 'notes']);

            $response = [
                'overall_score' => $metric?->overall_score ?? null,
                'efficiency_metrics' => [
                    'on_time' => $metric?->on_time_percentage ?? null,
                    'fuel_efficiency' => $metric?->fuel_efficiency ?? null,
                    'safety_violations' => $metric?->safety_violations ?? 0,
                    'customer_satisfaction' => $metric?->customer_satisfaction ?? null,
                ],
                'monthly_trip_history' => $months,
                'recent_feedback' => $recentFeedback,
            ];

            return response()->json(['success' => true, 'data' => $response]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching performance.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/documents
    // Get list of uploaded documents for a staff member
    // ─────────────────────────────────────────────────
    public function documents(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            // Staff ke saare documents fetch karna aur map karke URLs banana
            $documents = \App\Models\StaffDocument::where('staff_id', $staff->id)
                ->latest()
                ->get()
                ->map(function ($doc) {
                    return [
                        'id'              => $doc->id,
                        'document_type'   => $doc->document_type,
                        'document_number' => $doc->document_number,
                        'expiry_date'     => $doc->expiry_date ? $doc->expiry_date->format('d-m-Y') : null,
                        'is_verified'     => $doc->is_verified,
                        'notes'           => $doc->notes,
                        'created_at'      => $doc->created_at->format('d-m-Y H:i A'),

                        // View aur Download dono ke liye public storage link
                        'view_url'        => asset("storage/{$doc->document_path}"),
                        'download_url'    => asset("storage/{$doc->document_path}")
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Staff documents retrieved successfully.',
                'data'    => $documents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching documents.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadDocument(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $request->validate([
            'document_type'   => 'required|in:aadhar,pan,license,photo,address_proof,bank_passbook,other',
            'document_number' => 'nullable|string|max:100',
            'expiry_date'     => 'nullable|date',
            'document_file'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'           => 'nullable|string',
        ], [
            'document_type.required' => 'Document type is required.',
            'document_file.required' => 'Document file is required.',
            'document_file.max'      => 'File size must not exceed 5MB.',
        ]);

        try {
            $doc = $this->service->uploadDocument(
                $staff,
                $request->only(['document_type', 'document_number', 'expiry_date', 'notes']),
                $request->file('document_file')
            );

            return response()->json([
                'success'  => true,
                'message'  => 'Document uploaded successfully.',
                'data'     => $doc,
                'file_url' => asset("storage/{$doc->document_path}"),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while uploading the document.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function dutyHistory(Staff $staff)
    {
        try {
            $query = StaffAttendance::where('staff_id', $staff->id);

            $logs = $query->latest('date')->paginate(30);

            $today = StaffAttendance::where('staff_id', $staff->id)->whereDate('date', now()->toDateString())->sum('total_hours');
            $weekly = StaffAttendance::where('staff_id', $staff->id)->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->toDateString()])->sum('total_hours');
            $monthly = StaffAttendance::where('staff_id', $staff->id)->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->toDateString()])->sum('total_hours');

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => ['today' => (float)$today, 'weekly' => (float)$weekly, 'monthly' => (float)$monthly],
                    'logs'    => $logs,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred while fetching duty history.', 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/v1/staff/{staff}/duty-logs
    public function storeDuty(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'date' => 'required|date',
            'in_time' => 'nullable|date_format:Y-m-d H:i:s',
            'out_time' => 'nullable|date_format:Y-m-d H:i:s',
            'total_hours' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            // Normalize date and time values to match DB columns
            $date = \Carbon\Carbon::parse($data['date'])->toDateString();
            $in_time = isset($data['in_time']) ? \Carbon\Carbon::parse($data['in_time'])->format('H:i:s') : null;
            $out_time = isset($data['out_time']) ? \Carbon\Carbon::parse($data['out_time'])->format('H:i:s') : null;

            $values = [
                'in_time' => $in_time,
                'out_time' => $out_time,
                'total_hours' => $data['total_hours'] ?? null,
                'status' => $data['status'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            // Use updateOrCreate to avoid duplicate unique (staff_id + date) errors
            $attendance = StaffAttendance::updateOrCreate(
                ['staff_id' => $staff->id, 'date' => $date],
                $values
            );

            return response()->json(['success' => true, 'message' => 'Duty record saved.', 'data' => $attendance], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add duty record.', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/v1/staff/{staff}/duty-hours
    public function getDutyHours(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $today = StaffAttendance::where('staff_id', $staff->id)->whereDate('date', now()->toDateString())->sum('total_hours');
            $weekly = StaffAttendance::where('staff_id', $staff->id)->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->toDateString()])->sum('total_hours');
            $monthly = StaffAttendance::where('staff_id', $staff->id)->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->toDateString()])->sum('total_hours');

            $logs = StaffAttendance::where('staff_id', $staff->id)->latest('date')->get(['date', 'status', 'in_time', 'out_time', 'total_hours', 'notes']);

            return response()->json(['success' => true, 'data' => ['summary' => ['today' => (float)$today, 'weekly' => (float)$weekly, 'monthly' => (float)$monthly], 'logs' => $logs]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch duty hours.', 'error' => $e->getMessage()], 500);
        }
    }


    public function salaryFilter(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'year'     => 'nullable|integer',
            'month'    => 'nullable|integer|min:1|max:12',
            'amount'   => 'nullable|numeric',
        ]);

        try {
            $staff = Staff::findOrFail($data['staff_id']);

            $query = StaffSalary::where('staff_id', $staff->id)
                ->when($request->year, fn($q, $v) => $q->where('year', $v))
                ->when($request->month, fn($q, $v) => $q->where('month', 'like', "%-" . str_pad($v, 2, '0', STR_PAD_LEFT)))
                ->when($request->amount, fn($q, $v) => $q->where('net_salary', $v));

            $salaries = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->get();

            // Image 11 ke top cards ke liye summary
            $summary = [
                'monthly_salary' => (float) $staff->basic_salary,
                'total_paid'     => (float) $query->clone()->where('payment_status', 'paid')->sum('net_salary'),
                'total_pending'  => (float) $query->clone()->where('payment_status', 'pending')->sum('net_salary'),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'staff_details' => [
                        'id'   => $staff->id,
                        'name' => $staff->name,
                        'role' => $staff->role?->name
                    ],
                    'summary' => $summary,
                    'history' => $salaries
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering salary records.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/salary
    // Salary history with Summary (For UI Image 11)
    // ─────────────────────────────────────────────────
    public function salaryList(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $query = StaffSalary::where('staff_id', $staff->id)
                ->when($request->year, fn($q, $v) => $q->where('year', $v))
                ->when($request->month, fn($q, $v) => $q->where('month', $v))
                ->when($request->payment_status, fn($q, $v) => $q->where('payment_status', $v));

            $salaries = (clone $query)->orderBy('year', 'desc')->orderBy('month', 'desc')->paginate($request->integer('per_page', 12));

            $totalPaid = (clone $query)->where('payment_status', 'paid')->sum('net_salary');
            $totalPending = (clone $query)->where('payment_status', 'pending')->sum('net_salary');

            return response()->json(['success' => true, 'message' => 'Salary records retrieved successfully.', 'data' => ['summary' => ['monthly_salary' => $staff->basic_salary, 'total_paid' => $totalPaid, 'total_pending' => $totalPending], 'financial_history' => $salaries]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred while fetching salary history.', 'error' => $e->getMessage()], 500);
        }
    }

    // POST /api/v1/staff/{staff}/pay-salary
    public function paySalaryForStaff(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'paid_on' => 'required|date',
            'transaction_ref' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $salary = StaffSalary::updateOrCreate(
                ['staff_id' => $staff->id, 'month' => $data['month'], 'year' => $data['year']],
                ['basic_salary' => $staff->basic_salary ?? 0, 'paid_on' => $data['paid_on'], 'payment_mode' => $data['payment_mode'], 'payment_status' => 'paid', 'transaction_ref' => $data['transaction_ref'] ?? null, 'notes' => $data['notes'] ?? null, 'net_salary' => $data['amount']]
            );

            return response()->json(['success' => true, 'message' => 'Salary paid successfully.', 'data' => $salary]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to record salary payment.', 'error' => $e->getMessage()], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
