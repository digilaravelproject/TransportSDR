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

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $staff = Staff::with(['user'])
                ->when($request->staff_type,   fn($q, $v) => $q->where('staff_type', $v))
                ->when($request->is_available, fn($q, $v) => $q->where('is_available', (bool)$v))
                ->when($request->is_active,    fn($q, $v) => $q->where('is_active', (bool)$v))
                ->when($request->search,       fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('name',  'like', "%{$v}%")
                        ->orWhere('phone', 'like', "%{$v}%")
                        ->orWhere('email', 'like', "%{$v}%");
                }))
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

    // ─────────────────────────────────────────────────
    // POST /api/v1/staff
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'phone'                  => 'required|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'required|in:driver,helper,office',
            'date_of_birth'          => 'nullable|date',
            'date_of_joining'        => 'nullable|date',
            'address'                => 'nullable|string',
            'emergency_contact'      => 'nullable|string|max:15',
            'emergency_contact_name' => 'nullable|string|max:255',
            'license_number'         => 'nullable|string|max:50',
            'license_expiry'         => 'nullable|date',
            'license_type'           => 'nullable|string|max:50',
            'basic_salary'           => 'nullable|numeric|min:0',
            'da_per_day'             => 'nullable|numeric|min:0',
            'hra'                    => 'nullable|numeric|min:0',
            'other_allowance'        => 'nullable|numeric|min:0',
            'bank_name'              => 'nullable|string|max:100',
            'bank_account'           => 'nullable|string|max:50',
            'bank_ifsc'              => 'nullable|string|max:20',
            'notes'                  => 'nullable|string',
        ], [
            'name.required'       => 'Staff name is required.',
            'phone.required'      => 'Phone number is required.',
            'staff_type.required' => 'Staff type is required.',
            'staff_type.in'       => 'Staff type must be driver, helper or office.',
        ]);

        try {
            $staff = $this->service->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Staff member added successfully.',
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

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}
    // ─────────────────────────────────────────────────
    public function show(Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $staff->load(['user', 'documents']);

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

    // ─────────────────────────────────────────────────
    // PUT /api/v1/staff/{id}
    // ─────────────────────────────────────────────────
    public function update(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name'                   => 'sometimes|string|max:255',
            'phone'                  => 'sometimes|string|max:15',
            'email'                  => 'nullable|email|max:255',
            'staff_type'             => 'sometimes|in:driver,helper,office',
            'date_of_birth'          => 'nullable|date',
            'date_of_joining'        => 'nullable|date',
            'address'                => 'nullable|string',
            'emergency_contact'      => 'nullable|string|max:15',
            'emergency_contact_name' => 'nullable|string|max:255',
            'license_number'         => 'nullable|string|max:50',
            'license_expiry'         => 'nullable|date',
            'license_type'           => 'nullable|string|max:50',
            'basic_salary'           => 'nullable|numeric|min:0',
            'da_per_day'             => 'nullable|numeric|min:0',
            'hra'                    => 'nullable|numeric|min:0',
            'other_allowance'        => 'nullable|numeric|min:0',
            'bank_name'              => 'nullable|string|max:100',
            'bank_account'           => 'nullable|string|max:50',
            'bank_ifsc'              => 'nullable|string|max:20',
            'is_available'           => 'boolean',
            'is_active'              => 'boolean',
            'notes'                  => 'nullable|string',
        ]);

        try {
            $staff = $this->service->update($staff, $data);

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully.',
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
    // POST /api/v1/staff/{id}/attendance
    // Mark attendance
    // ─────────────────────────────────────────────────
    public function markAttendance(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'date'      => 'required|date',
            'status'    => 'required|in:present,absent,half_day,on_trip,leave,holiday',
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'notes'     => 'nullable|string',
        ], [
            'date.required'   => 'Attendance date is required.',
            'status.required' => 'Attendance status is required.',
            'status.in'       => 'Invalid attendance status.',
        ]);

        try {
            $attendance = $this->service->markAttendance($staff, array_merge($data, [
                'staff_id' => $staff->id,
            ]));

            return response()->json([
                'success' => true,
                'message' => "Attendance marked as {$attendance->status} for {$attendance->date->format('d-m-Y')}.",
                'data'    => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while marking attendance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/attendance
    // Attendance list with filters
    // ─────────────────────────────────────────────────
    public function attendanceList(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $month = $request->month ?? now()->format('m');
            $year  = $request->year  ?? now()->format('Y');

            $attendance = StaffAttendance::where('staff_id', $staff->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();

            $summary = [
                'total_days'   => $attendance->count(),
                'present'      => $attendance->where('status', 'present')->count(),
                'absent'       => $attendance->where('status', 'absent')->count(),
                'half_day'     => $attendance->where('status', 'half_day')->count(),
                'on_trip'      => $attendance->where('status', 'on_trip')->count(),
                'leave'        => $attendance->where('status', 'leave')->count(),
                'holiday'      => $attendance->where('status', 'holiday')->count(),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'staff'      => ['id' => $staff->id, 'name' => $staff->name],
                    'period'     => ['month' => $month, 'year' => $year],
                    'summary'    => $summary,
                    'attendance' => $attendance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching attendance list.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/staff/{id}/da
    // Calculate DA for a specific trip
    // ─────────────────────────────────────────────────
    public function calculateDA(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'trip_id'         => 'required|exists:trips,id',
            'extra_allowance' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ], [
            'trip_id.required' => 'Trip is required.',
            'trip_id.exists'   => 'Trip not found.',
        ]);

        try {
            $trip = Trip::findOrFail($data['trip_id']);

            // Check staff was on this trip
            if ($trip->driver_id !== $staff->id && $trip->helper_id !== $staff->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This staff member was not assigned to this trip.',
                ], 422);
            }

            $daLog = $this->service->calculateTripDA($staff, $trip);

            // Update extra allowance if provided
            if (isset($data['extra_allowance'])) {
                $daLog->update(['extra_allowance' => $data['extra_allowance']]);
            }

            return response()->json([
                'success' => true,
                'message' => "DA calculated: ₹{$daLog->da_amount} for {$trip->trip_number}.",
                'data'    => $daLog->load('trip'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while calculating DA.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/da
    // DA logs list
    // ─────────────────────────────────────────────────
    public function daList(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $daLogs = StaffDaLog::where('staff_id', $staff->id)
                ->with('trip')
                ->when($request->status, fn($q, $v) => $q->where('status', $v))
                ->latest()
                ->paginate(20);

            $summary = [
                'total_pending' => StaffDaLog::where('staff_id', $staff->id)->where('status', 'pending')->sum('da_amount'),
                'total_paid'    => StaffDaLog::where('staff_id', $staff->id)->where('status', 'paid')->sum('da_amount'),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'summary' => $summary,
                    'logs'    => $daLogs,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching DA list.',
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
            'payment_mode'    => 'required|in:cash,bank,upi',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ], [
            'amount.required'       => 'Advance amount is required.',
            'advance_date.required' => 'Advance date is required.',
            'payment_mode.required' => 'Payment mode is required.',
        ]);

        try {
            $advance = StaffAdvance::create(array_merge($data, [
                'staff_id' => $staff->id,
            ]));

            return response()->json([
                'success' => true,
                'message' => "Advance of ₹{$advance->amount} given to {$staff->name}.",
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
            $advances = StaffAdvance::where('staff_id', $staff->id)
                ->when($request->is_deducted, fn($q, $v) => $q->where('is_deducted', (bool)$v))
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data'    => [
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
    // POST /api/v1/staff/{id}/salary/generate
    // Generate monthly salary
    // ─────────────────────────────────────────────────
    public function generateSalary(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020',
            'bonus'          => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ], [
            'month.required' => 'Month is required.',
            'year.required'  => 'Year is required.',
        ]);

        try {
            $salary = $this->service->generateSalary($staff, $data['year'], $data['month']);

            // Add bonus and other deduction if provided
            if (isset($data['bonus']) || isset($data['other_deduction'])) {
                $salary->update([
                    'bonus'           => $data['bonus']           ?? $salary->bonus,
                    'other_deduction' => $data['other_deduction'] ?? $salary->other_deduction,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Salary generated for {$staff->name} — {$salary->month}.",
                'data'    => $salary->load('staff'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating salary.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/staff/{id}/salary/{salaryId}/pay
    // Mark salary as paid
    // ─────────────────────────────────────────────────
    public function paySalary(Request $request, Staff $staff, StaffSalary $salary)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        if ($salary->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Salary already paid for this month.',
            ], 422);
        }

        $data = $request->validate([
            'payment_mode'    => 'required|in:cash,bank,upi',
            'paid_on'         => 'required|date',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ], [
            'payment_mode.required' => 'Payment mode is required.',
            'paid_on.required'      => 'Payment date is required.',
        ]);

        try {
            $salary = $this->service->markSalaryPaid($salary, $data);

            return response()->json([
                'success' => true,
                'message' => "Salary of ₹{$salary->net_salary} paid to {$staff->name}.",
                'data'    => $salary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while marking the salary as paid.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/salary
    // Salary history
    // ─────────────────────────────────────────────────
    public function salaryList(Request $request, Staff $staff)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $salaries = StaffSalary::where('staff_id', $staff->id)
                ->when($request->year, fn($q, $v) => $q->where('year', $v))
                ->when($request->payment_status, fn($q, $v) => $q->where('payment_status', $v))
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(12);

            return response()->json([
                'success' => true,
                'data'    => $salaries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching salary history.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/staff/{id}/salary-slip/{salaryId}
    // Download salary slip PDF
    // ─────────────────────────────────────────────────
    public function salarySlip(Staff $staff, StaffSalary $salary)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $salary->load('staff');
            $tenant = auth()->user()->tenant;

            $absoluteDir = storage_path(
                'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                    'tenants' . DIRECTORY_SEPARATOR . $staff->tenant_id . DIRECTORY_SEPARATOR . 'salary-slips'
            );

            $fileName     = "salary-{$staff->id}-{$salary->month}.pdf";
            $absoluteFile = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

            if (!\Illuminate\Support\Facades\File::exists($absoluteDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($absoluteDir, 0775, true);
            }

            \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.salary-slip', [
                'staff'  => $staff,
                'salary' => $salary,
                'tenant' => $tenant,
            ])->setPaper('a4')->save($absoluteFile);

            return response()->file($absoluteFile, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"salary-{$staff->name}-{$salary->month}.pdf\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the salary slip.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/staff/{id}/document
    // Upload staff document
    // ─────────────────────────────────────────────────
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
            $month = $request->month ?? now()->format('m');
            $year  = $request->year  ?? now()->format('Y');

            $staff = Staff::withCount([
                'driverTrips as driver_trips_count' => fn($q) => $q->whereMonth('trip_date', $month)->whereYear('trip_date', $year),
                'helperTrips as helper_trips_count' => fn($q) => $q->whereMonth('trip_date', $month)->whereYear('trip_date', $year),
            ])->get()->map(function ($s) use ($month, $year) {
                $presentDays = StaffAttendance::where('staff_id', $s->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->whereIn('status', ['present', 'on_trip'])
                    ->count();

                return [
                    'id'           => $s->id,
                    'name'         => $s->name,
                    'type'         => $s->staff_type,
                    'total_trips'  => $s->driver_trips_count + $s->helper_trips_count,
                    'present_days' => $presentDays,
                    'is_available' => $s->is_available,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => [
                    'period'      => ['month' => $month, 'year' => $year],
                    'performance' => $staff,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching performance data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
