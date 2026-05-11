<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Staff;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // GET /api/v1/reports/stats?month=YYYY-MM or ?from=&to=
    public function stats(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $request->validate([
            'month' => ['nullable','regex:/^\d{4}-\d{2}$/'],
            'from'  => 'nullable|date',
            'to'    => 'nullable|date|after_or_equal:from',
        ]);

        if ($request->month) {
            $from = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()->toDateString();
            $to   = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->endOfMonth()->toDateString();
        } else {
            $from = $request->from ?? now()->startOfMonth()->toDateString();
            $to   = $request->to   ?? now()->endOfMonth()->toDateString();
        }

        $tenantId = auth()->user()->tenant_id ?? null;

        // Total trips in period
        $totalTrips = Trip::where('tenant_id', $tenantId)->whereBetween('trip_date', [$from, $to])->count();

        // Trip revenue: sum total_amount for completed trips (or all trips depending on requirement)
        $tripRevenue = Trip::where('tenant_id', $tenantId)->whereBetween('trip_date', [$from, $to])->sum('total_amount');

        // Active vehicles
        $activeVehicles = Vehicle::where('tenant_id', $tenantId)->where('is_active', true)->count();

        // Active drivers
        $activeDrivers = Staff::where('tenant_id', $tenantId)->where('is_active', true)->where(function ($q) {
            $q->where('staff_type', 'driver')->orWhere('work_shift', 'driver');
        })->count();

        // Recent reports (last 5)
        $recentReports = Report::where('tenant_id', $tenantId)->latest()->limit(5)->get(['id','name','type','format','status','file_path','created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'total_trips' => $totalTrips,
                'trip_revenue' => round((float)$tripRevenue, 2),
                'active_vehicles' => $activeVehicles,
                'active_drivers' => $activeDrivers,
                'recent_reports' => $recentReports,
                'period' => ['from' => $from, 'to' => $to],
            ],
        ]);
    }

    // GET /api/v1/reports
    // Optional query: ?type=trip|financial|vehicle|staff|custom & ?page
    public function list(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $type = $request->query('type');
        $tenantId = auth()->user()->tenant_id ?? null;

        $q = Report::where('tenant_id', $tenantId);
        if ($type) $q->where('type', $type);

        $reports = $q->latest()->paginate($request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $reports]);
    }

    // GET /api/v1/reports/monthly?type=trip&month=YYYY-MM
    // If a report for the month exists return it, otherwise generate synchronously and return
    public function monthly(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $request->validate([
            'type' => 'required|string',
            'month' => ['required','regex:/^\d{4}-\d{2}$/'],
        ]);

        $type = $request->type;
        $month = $request->month;
        $tenantId = auth()->user()->tenant_id ?? null;

        $from = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $to   = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        // Try find existing report for this type & month
        $existing = Report::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->first();

        if ($existing && $existing->status === 'ready' && $existing->file_path) {
            return response()->json(['success' => true, 'data' => $existing]);
        }

        // Not found -> generate one synchronously by reusing generate logic
        $name = ucfirst($type) . " Report - " . $month;

        $report = Report::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'type' => $type,
            'format' => 'pdf',
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        try {
            $trips = Trip::where('tenant_id', $tenantId)->whereBetween('trip_date', [$from, $to])->where('status','completed')->get();
            $summary = ['total_trips' => $trips->count(), 'total_revenue' => $trips->sum('total_amount')];

            $pdf = Pdf::loadView('pdf.report', ['report' => $report, 'summary' => $summary, 'trips' => $trips]);

            $dir = "tenants/{$tenantId}/reports";
            $fileName = "report-{$report->id}.pdf";
            $path = "{$dir}/{$fileName}";

            Storage::disk('public')->put($path, $pdf->output());

            $report->update(['file_path' => $path, 'status' => 'ready']);

            return response()->json(['success' => true, 'data' => $report], 201);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Failed to generate monthly report', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/v1/reports/{report}/download
    public function download(Report $report)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $tenantId = auth()->user()->tenant_id ?? null;
        if ($report->tenant_id !== $tenantId) abort(403, 'Not allowed');

        if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
            return response()->json(['success' => false, 'message' => 'Report file not available'], 404);
        }

        return Storage::disk('public')->download($report->file_path, basename($report->file_path));
    }

    // POST /api/v1/reports/generate
    public function generate(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'format' => 'nullable|in:pdf,xlsx,csv',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $tenantId = auth()->user()->tenant_id ?? null;
        $userId = auth()->id();

        $report = Report::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'custom',
            'format' => $data['format'] ?? 'pdf',
            'status' => 'pending',
            'created_by' => $userId,
        ]);

        try {
            // Simple report generation: collect trip summary for period and render PDF
            $from = $data['from'] ?? now()->startOfMonth()->toDateString();
            $to   = $data['to'] ?? now()->endOfMonth()->toDateString();

            $trips = Trip::where('tenant_id', $tenantId)->whereBetween('trip_date', [$from, $to])->where('status','completed')->get();

            $summary = [
                'total_trips' => $trips->count(),
                'total_revenue' => $trips->sum('total_amount'),
            ];

            // generate PDF using a simple blade
            $pdf = Pdf::loadView('pdf.report', ['report' => $report, 'summary' => $summary, 'trips' => $trips]);

            $dir = "tenants/{$tenantId}/reports";
            $fileName = "report-{$report->id}.pdf";
            $path = "{$dir}/{$fileName}";

            Storage::disk('public')->put($path, $pdf->output());

            $report->update(['file_path' => $path, 'status' => 'ready']);

            return response()->json(['success' => true, 'message' => 'Report generated', 'data' => $report], 201);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Failed to generate report', 'error' => $e->getMessage()], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) abort(403, 'You do not have permission');
    }
}
