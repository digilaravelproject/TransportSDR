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
