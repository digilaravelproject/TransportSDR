<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Corporate, CorporateDuty, CorporatePayment, CorporateFine};
use App\Services\CorporateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CorporateController extends Controller
{
    public function __construct(private CorporateService $service) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $corporates = Corporate::withCount(['duties', 'payments'])
                ->when($request->duty_type,    fn($q, $v) => $q->where('duty_type', $v))
                ->when($request->is_active,    fn($q, $v) => $q->where('is_active', (bool)$v))
                ->when($request->search,       fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('company_name', 'like', "%{$v}%")
                        ->orWhere('phone', 'like', "%{$v}%")
                        ->orWhere('email', 'like', "%{$v}%");
                }))
                ->latest()
                ->paginate($request->per_page ?? 20)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'data'    => $corporates,
                'meta'    => [
                    'total'        => $corporates->total(),
                    'current_page' => $corporates->currentPage(),
                    'last_page'    => $corporates->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the corporate list.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/corporate
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'company_name'     => 'required|string|max:255',
            'contact_person'   => 'nullable|string|max:255',
            'phone'            => 'required|string|max:15',
            'email'            => 'nullable|email',
            'address'          => 'nullable|string',
            'gstin'            => 'nullable|string|max:15',
            'pan'              => 'nullable|string|max:10',
            'contract_type'    => 'required|in:monthly,daily,trip_based',
            'monthly_package'  => 'nullable|numeric|min:0',
            'per_day_rate'     => 'nullable|numeric|min:0',
            'per_km_rate'      => 'nullable|numeric|min:0',
            'extra_hour_rate'  => 'nullable|numeric|min:0',
            'holiday_rate'     => 'nullable|numeric|min:0',
            'extra_duty_rate'  => 'nullable|numeric|min:0',
            'included_km'      => 'nullable|numeric|min:0',
            'included_hours'   => 'nullable|integer|min:0',
            'vehicle_type'     => 'nullable|string|max:100',
            'number_of_vehicles' => 'nullable|integer|min:1',
            'duty_type'        => 'required|in:general,shift,shuttle',
            'is_gst'           => 'boolean',
            'gst_percent'      => 'nullable|numeric|min:0|max:28',
            'contract_start'   => 'nullable|date',
            'contract_end'     => 'nullable|date|after:contract_start',
            'notes'            => 'nullable|string',
        ], [
            'company_name.required'  => 'Company name is required.',
            'phone.required'         => 'Phone number is required.',
            'contract_type.required' => 'Contract type is required.',
            'duty_type.required'     => 'Duty type is required.',
        ]);

        try {
            $corporate = Corporate::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Corporate company added successfully.',
                'data'    => $corporate,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the corporate company.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}
    // ─────────────────────────────────────────────────
    public function show(Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $corporate->loadCount(['duties', 'payments']);

            $pendingFines   = $corporate->pendingFinesAmount();
            $lastPayment    = $corporate->payments()->latest()->first();
            $todayDuties    = $corporate->duties()->whereDate('duty_date', today())->count();
            $monthlyRevenue = $corporate->payments()
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');

            return response()->json([
                'success' => true,
                'data'    => [
                    'corporate'       => $corporate,
                    'pending_fines'   => $pendingFines,
                    'last_payment'    => $lastPayment,
                    'today_duties'    => $todayDuties,
                    'monthly_revenue' => $monthlyRevenue,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching corporate details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PUT /api/v1/corporate/{id}
    // ─────────────────────────────────────────────────
    public function update(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'company_name'      => 'sometimes|string|max:255',
            'contact_person'    => 'nullable|string|max:255',
            'phone'             => 'sometimes|string|max:15',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string',
            'gstin'             => 'nullable|string|max:15',
            'pan'               => 'nullable|string|max:10',
            'contract_type'     => 'sometimes|in:monthly,daily,trip_based',
            'monthly_package'   => 'nullable|numeric|min:0',
            'per_day_rate'      => 'nullable|numeric|min:0',
            'per_km_rate'       => 'nullable|numeric|min:0',
            'extra_hour_rate'   => 'nullable|numeric|min:0',
            'holiday_rate'      => 'nullable|numeric|min:0',
            'extra_duty_rate'   => 'nullable|numeric|min:0',
            'included_km'       => 'nullable|numeric|min:0',
            'included_hours'    => 'nullable|integer|min:0',
            'vehicle_type'      => 'nullable|string|max:100',
            'number_of_vehicles' => 'nullable|integer|min:1',
            'duty_type'         => 'sometimes|in:general,shift,shuttle',
            'is_gst'            => 'boolean',
            'gst_percent'       => 'nullable|numeric|min:0|max:28',
            'contract_start'    => 'nullable|date',
            'contract_end'      => 'nullable|date',
            'is_active'         => 'boolean',
            'notes'             => 'nullable|string',
        ]);

        try {
            $corporate->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Corporate updated successfully.',
                'data'    => $corporate->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating corporate details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/corporate/{id}
    // ─────────────────────────────────────────────────
    public function destroy(Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin']);

        try {
            $activeDuties = $corporate->duties()->where('duty_status', 'ongoing')->count();
            if ($activeDuties > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete. There are ongoing duties for this company.',
                ], 422);
            }

            $corporate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Corporate deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the corporate.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}/duties
    // List duties with filters
    // ─────────────────────────────────────────────────
    public function duties(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            $duties = CorporateDuty::where('corporate_id', $corporate->id)
                ->with(['vehicle', 'driver'])
                ->when($request->duty_status, fn($q, $v) => $q->where('duty_status', $v))
                ->when($request->duty_type,   fn($q, $v) => $q->where('duty_type', $v))
                ->when($request->from,        fn($q, $v) => $q->whereDate('duty_date', '>=', $v))
                ->when($request->to,          fn($q, $v) => $q->whereDate('duty_date', '<=', $v))
                ->when($request->is_holiday,  fn($q, $v) => $q->where('is_holiday', (bool)$v))
                ->latest('duty_date')
                ->paginate($request->per_page ?? 30);

            return response()->json([
                'success' => true,
                'data'    => $duties,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching duties.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/corporate/{id}/duty
    // Add duty entry
    // ─────────────────────────────────────────────────
    public function addDuty(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'duty_date'          => 'required|date',
            'duty_type'          => 'required|in:general,shift,shuttle',
            'shift_name'         => 'nullable|string|max:50',
            'shift_start'        => 'nullable|date_format:H:i',
            'shift_end'          => 'nullable|date_format:H:i',
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'vehicle_type'       => 'nullable|string|max:100',
            'number_of_vehicles' => 'nullable|integer|min:1',
            'driver_id'          => 'nullable|exists:staff,id',
            'helper_id'          => 'nullable|exists:staff,id',
            'pickup_location'    => 'nullable|string|max:255',
            'drop_location'      => 'nullable|string|max:255',
            'route_details'      => 'nullable|string',
            'start_km'           => 'nullable|numeric|min:0',
            'end_km'             => 'nullable|numeric|min:0|gte:start_km',
            'total_hours'        => 'nullable|numeric|min:0',
            'extra_hours'        => 'nullable|numeric|min:0',
            'is_holiday'         => 'boolean',
            'is_extra_duty'      => 'boolean',
            'duty_status'        => 'nullable|in:scheduled,ongoing,completed,cancelled',
            'notes'              => 'nullable|string',
        ], [
            'duty_date.required' => 'Duty date is required.',
            'duty_type.required' => 'Duty type is required.',
        ]);

        try {
            $duty = CorporateDuty::create(array_merge($data, [
                'corporate_id' => $corporate->id,
            ]));

            // Preview billing calculation
            $billing = $this->service->calculateDutyBilling($duty->fresh());

            return response()->json([
                'success'  => true,
                'message'  => "Duty {$duty->duty_number} added successfully.",
                'data'     => $duty->load(['vehicle', 'driver']),
                'billing'  => $billing,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the duty.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/corporate/{id}/duty/{duty}
    // Update duty (KM, status etc.)
    // ─────────────────────────────────────────────────
    public function updateDuty(Request $request, Corporate $corporate, CorporateDuty $duty)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'duty_status'   => 'sometimes|in:scheduled,ongoing,completed,cancelled',
            'start_km'      => 'nullable|numeric|min:0',
            'end_km'        => 'nullable|numeric|min:0|gte:start_km',
            'total_hours'   => 'nullable|numeric|min:0',
            'extra_hours'   => 'nullable|numeric|min:0',
            'is_holiday'    => 'boolean',
            'is_extra_duty' => 'boolean',
            'driver_id'     => 'nullable|exists:staff,id',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'notes'         => 'nullable|string',
        ]);

        try {
            $duty->update($data);

            $billing = $this->service->calculateDutyBilling($duty->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Duty updated successfully.',
                'data'    => $duty->fresh(['vehicle', 'driver']),
                'billing' => $billing,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the duty.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/corporate/{id}/fine
    // Add fine
    // ─────────────────────────────────────────────────
    public function addFine(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'reason'   => 'required|string|max:255',
            'amount'   => 'required|numeric|min:1',
            'fine_date' => 'required|date',
            'duty_id'  => 'nullable|exists:corporate_duties,id',
            'notes'    => 'nullable|string',
        ], [
            'reason.required'    => 'Fine reason is required.',
            'amount.required'    => 'Fine amount is required.',
            'fine_date.required' => 'Fine date is required.',
        ]);

        try {
            $fine = CorporateFine::create(array_merge($data, [
                'corporate_id' => $corporate->id,
            ]));

            // Update duty fine amount if duty_id given
            if (!empty($data['duty_id'])) {
                $duty = CorporateDuty::find($data['duty_id']);
                if ($duty) {
                    $totalFine = $duty->fines()->where('status', 'pending')->sum('amount');
                    $duty->update(['fine_amount' => $totalFine]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Fine of ₹{$fine->amount} added for {$corporate->company_name}.",
                'data'    => $fine,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the fine.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}/fines
    // List fines
    // ─────────────────────────────────────────────────
    public function fines(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $fines = CorporateFine::where('corporate_id', $corporate->id)
                ->with(['duty'])
                ->when($request->status, fn($q, $v) => $q->where('status', $v))
                ->latest('fine_date')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data'    => [
                    'pending_amount' => $corporate->pendingFinesAmount(),
                    'fines'          => $fines,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching fines.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/corporate/{id}/fine/{fine}/waive
    // Waive fine
    // ─────────────────────────────────────────────────
    public function waiveFine(Request $request, Corporate $corporate, CorporateFine $fine)
    {
        $this->checkRole(['superadmin', 'admin']);

        if ($fine->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending fines can be waived.',
            ], 422);
        }

        try {
            $fine->update(['status' => 'waived', 'notes' => $request->notes]);

            return response()->json([
                'success' => true,
                'message' => 'Fine waived successfully.',
                'data'    => $fine->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while waiving the fine.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/corporate/{id}/generate-invoice
    // Generate monthly invoice
    // ─────────────────────────────────────────────────
    public function generateInvoice(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ], [
            'from.required' => 'Billing from date is required.',
            'to.required'   => 'Billing to date is required.',
        ]);

        try {
            $payment = $this->service->generateMonthlyInvoice($corporate, $data['from'], $data['to']);

            return response()->json([
                'success' => true,
                'message' => "Invoice {$payment->invoice_number} generated. Total: ₹{$payment->total_amount}",
                'data'    => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the invoice.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}/invoice/{payment}
    // Download invoice PDF
    // ─────────────────────────────────────────────────
    public function downloadInvoice(Corporate $corporate, CorporatePayment $payment)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $absolutePath = $this->service->generateInvoicePdf($payment);

            if (!file_exists($absolutePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice PDF could not be generated.',
                ], 500);
            }

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$payment->invoice_number}.pdf\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing the PDF download.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/corporate/{id}/payment/{payment}/pay
    // Record payment received
    // ─────────────────────────────────────────────────
    public function recordPayment(Request $request, Corporate $corporate, CorporatePayment $payment)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'paid_amount'     => 'required|numeric|min:1',
            'payment_mode'    => 'required|in:cash,bank,cheque,upi',
            'paid_on'         => 'required|date',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ], [
            'paid_amount.required'  => 'Paid amount is required.',
            'payment_mode.required' => 'Payment mode is required.',
            'paid_on.required'      => 'Payment date is required.',
        ]);

        try {
            $newPaid = $payment->paid_amount + $data['paid_amount'];

            $payment->update([
                'paid_amount'     => $newPaid,
                'payment_mode'    => $data['payment_mode'],
                'paid_on'         => $data['paid_on'],
                'transaction_ref' => $data['transaction_ref'] ?? $payment->transaction_ref,
                'notes'           => $data['notes'] ?? $payment->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹{$data['paid_amount']} recorded. Balance: ₹{$payment->fresh()->balance_amount}",
                'data'    => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while recording the payment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}/payments
    // Payment history
    // ─────────────────────────────────────────────────
    public function payments(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $payments = CorporatePayment::where('corporate_id', $corporate->id)
                ->when($request->payment_status, fn($q, $v) => $q->where('payment_status', $v))
                ->latest()
                ->paginate(20);

            $summary = [
                'total_invoiced' => CorporatePayment::where('corporate_id', $corporate->id)->sum('total_amount'),
                'total_paid'     => CorporatePayment::where('corporate_id', $corporate->id)->sum('paid_amount'),
                'total_pending'  => CorporatePayment::where('corporate_id', $corporate->id)->where('payment_status', '!=', 'paid')->sum('balance_amount'),
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'summary'  => $summary,
                    'payments' => $payments,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching payment history.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/corporate/{id}/report
    // Monthly report
    // ─────────────────────────────────────────────────
    public function report(Request $request, Corporate $corporate)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $from = $request->from ?? now()->startOfMonth()->toDateString();
            $to   = $request->to   ?? now()->toDateString();

            $duties = CorporateDuty::where('corporate_id', $corporate->id)
                ->whereBetween('duty_date', [$from, $to])
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'corporate'  => $corporate,
                    'period'     => ['from' => $from, 'to' => $to],
                    'summary'    => [
                        'total_duties'       => $duties->count(),
                        'completed_duties'   => $duties->where('duty_status', 'completed')->count(),
                        'cancelled_duties'   => $duties->where('duty_status', 'cancelled')->count(),
                        'holiday_duties'     => $duties->where('is_holiday', true)->count(),
                        'extra_duties'       => $duties->where('is_extra_duty', true)->count(),
                        'total_km'           => round($duties->sum('total_km'), 2),
                        'extra_km'           => round($duties->sum('extra_km'), 2),
                        'total_extra_km_amt' => round($duties->sum('extra_km_amount'), 2),
                        'total_holiday_amt'  => round($duties->sum('holiday_amount'), 2),
                        'total_fines'        => $corporate->pendingFinesAmount(),
                    ],
                    'duties' => $duties->load(['vehicle', 'driver']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the report.',
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
