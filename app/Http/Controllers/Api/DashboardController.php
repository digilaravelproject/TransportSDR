<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Exception;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/dashboard/summary
    // KPIs — trips, leads, vehicles, revenue
    // ─────────────────────────────────────────────────
    public function summary()
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->getSummary(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the dashboard summary.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/dashboard/charts
    // All chart data
    // ─────────────────────────────────────────────────
    public function charts()
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->getCharts(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching dashboard charts.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/dashboard/pl-report
    // P&L report — ?from=2026-04-01&to=2026-04-30
    // ─────────────────────────────────────────────────
    public function plReport(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        try {
            $from = $request->from ?? now()->startOfMonth()->toDateString();
            $to   = $request->to   ?? now()->toDateString();

            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->getProfitLoss($from, $to),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the P&L report.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/dashboard/performance
    // ?period=today|week|month|year
    // ─────────────────────────────────────────────────
    public function performance(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'period' => 'nullable|in:today,week,month,year',
        ]);

        try {
            $period = $request->period ?? 'month';

            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->getPerformance($period),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching performance data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/dashboard/notifications
    // All alerts and notifications
    // ─────────────────────────────────────────────────
    public function notifications()
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        try {
            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->getNotifications(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching notifications.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/v1/dashboard/finance?month=YYYY-MM or ?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function financeOverview(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'month' => ['nullable','regex:/^\d{4}-\d{2}$/'],
            'from'  => 'nullable|date',
            'to'    => 'nullable|date|after_or_equal:from',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        try {
            if ($request->month) {
                $from = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()->toDateString();
                $to   = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->endOfMonth()->toDateString();
            } else {
                $from = $request->from ?? now()->startOfMonth()->toDateString();
                $to   = $request->to   ?? now()->endOfMonth()->toDateString();
            }

            $tenantId = auth()->user()->tenant_id ?? null;

            // Total revenue: sum of payments received for trips in period
            $revenueQuery = \DB::table('trip_payments')
                ->join('trips', 'trip_payments.trip_id', '=', 'trips.id')
                ->whereBetween('trips.trip_date', [$from, $to])
                ->where('trips.tenant_id', $tenantId)
                ->selectRaw('COALESCE(SUM(trip_payments.amount),0) as total');

            $totalRevenue = (float) $revenueQuery->value('total');

            // Total expenses: payments labelled as expense (best-effort)
            $expenseTypes = ['expense', 'vendor', 'cost', 'outgoing'];
            $expenseQuery = \DB::table('trip_payments')
                ->join('trips', 'trip_payments.trip_id', '=', 'trips.id')
                ->whereBetween('trips.trip_date', [$from, $to])
                ->where('trips.tenant_id', $tenantId)
                ->whereIn('trip_payments.type', $expenseTypes)
                ->selectRaw('COALESCE(SUM(trip_payments.amount),0) as total');

            $totalExpenses = (float) $expenseQuery->value('total');

            // Pending amount: sum of balance_amount for trips in period that are not fully paid
            $pendingAmount = (float) \App\Models\Trip::whereBetween('trip_date', [$from, $to])
                ->where('tenant_id', $tenantId)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->sum('balance_amount');

            // Completed trips details
            $limit = $request->integer('limit', 50);
            $completedTrips = \App\Models\Trip::with(['vehicle', 'driver', 'customer'])
                ->whereBetween('trip_date', [$from, $to])
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->orderBy('trip_date', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($trip) {
                    return [
                        'id' => $trip->id,
                        'trip_number' => $trip->trip_number,
                        'trip_date' => $trip->trip_date?->toDateString(),
                        'route' => $trip->trip_route,
                        'customer' => $trip->customer_name,
                        'vehicle' => $trip->vehicle?->registration_number,
                        'driver' => $trip->driver?->name,
                        'revenue' => (float) $trip->total_amount,
                        'tax' => (float) $trip->tax_amount,
                        'discount' => (float) $trip->discount,
                        'paid' => (float) ($trip->advance_amount + $trip->part_payment),
                        'balance' => (float) $trip->balance_amount,
                        'payment_status' => $trip->payment_status,
                    ];
                })->values();

            $totalProfit = $totalRevenue - $totalExpenses;

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => ['from' => $from, 'to' => $to],
                    'total_revenue' => round($totalRevenue, 2),
                    'total_expenses' => round($totalExpenses, 2),
                    'total_profit' => round($totalProfit, 2),
                    'pending_amount' => round($pendingAmount, 2),
                    'completed_trips' => $completedTrips,
                    'completed_count' => $completedTrips->count(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error computing finance overview', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/dashboard/clear-cache
    // Admin manually clear dashboard cache
    // ─────────────────────────────────────────────────
    public function clearCache()
    {
        $this->checkRole(['superadmin', 'admin']);

        try {
            $tenantId = auth()->user()->tenant_id;
            $patterns = [
                "dashboard:kpis:{$tenantId}:*",
                "dashboard:trip-status:{$tenantId}",
                "dashboard:lead-funnel:{$tenantId}",
                "dashboard:monthly-trip-revenue:{$tenantId}:*",
                "dashboard:vehicle-performance:{$tenantId}",
                "dashboard:revenue-source:{$tenantId}:*",
                "dashboard:fuel-efficiency:{$tenantId}",
                "dashboard:pl:{$tenantId}:*",
                "dashboard:vehicle-pl:{$tenantId}:*",
                "dashboard:pl-trend:{$tenantId}:*",
                "dashboard:performance:{$tenantId}:*",
                "dashboard:notifications:{$tenantId}:*",
            ];

            foreach ($patterns as $key) {
                Cache::forget($key);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache cleared successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while clearing the dashboard cache.',
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
