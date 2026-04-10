<?php

namespace App\Services\Dashboard;

use App\Models\{Trip, Lead, Vehicle, VehicleFuelLog};
use Illuminate\Support\Facades\{Cache, DB};
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ChartService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    // ── Trips status chart ─────────────────────────────
    public function getTripStatusChart(): array
    {
        return Cache::remember(
            "dashboard:trip-status:{$this->tenantId}",
            now()->addMinutes(5),
            fn() => [
                'labels' => ['Scheduled', 'Ongoing', 'Completed', 'Cancelled'],
                'data'   => [
                    Trip::where('status', 'scheduled')->count(),
                    Trip::where('status', 'ongoing')->count(),
                    Trip::where('status', 'completed')->count(),
                    Trip::where('status', 'cancelled')->count(),
                ],
                'colors' => ['#378ADD', '#EF9F27', '#1D9E75', '#E24B4A'],
            ]
        );
    }

    // ── Lead funnel chart ──────────────────────────────
    public function getLeadFunnelChart(): array
    {
        return Cache::remember(
            "dashboard:lead-funnel:{$this->tenantId}",
            now()->addMinutes(5),
            fn() => [
                'labels' => ['New', 'Contacted', 'Followup', 'Quoted', 'Confirmed', 'Converted', 'Lost'],
                'data'   => [
                    Lead::where('status', 'new')->count(),
                    Lead::where('status', 'contacted')->count(),
                    Lead::where('status', 'followup')->count(),
                    Lead::where('status', 'quoted')->count(),
                    Lead::where('status', 'confirmed')->count(),
                    Lead::where('status', 'converted')->count(),
                    Lead::where('status', 'lost')->count(),
                ],
                'colors' => ['#85B7EB', '#378ADD', '#EF9F27', '#AFA9EC', '#1D9E75', '#27500A', '#E24B4A'],
            ]
        );
    }

    // ── Monthly trips + revenue chart ─────────────────
    public function getMonthlyTripRevenueChart(int $months = 12): array
    {
        $cacheKey = "dashboard:monthly-trip-revenue:{$this->tenantId}:{$months}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($months) {
            $start  = now()->subMonths($months - 1)->startOfMonth();
            $end    = now()->endOfMonth();

            $trips = Trip::whereBetween('trip_date', [$start, $end])
                ->select(
                    DB::raw('YEAR(trip_date) as year'),
                    DB::raw('MONTH(trip_date) as month'),
                    DB::raw('COUNT(*) as total_trips'),
                    DB::raw('SUM(total_amount) as total_revenue')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn($r) => "{$r->year}-{$r->month}");

            $labels  = [];
            $tripData    = [];
            $revenueData = [];

            $period = CarbonPeriod::create($start, '1 month', $end);

            foreach ($period as $date) {
                $key     = "{$date->year}-{$date->month}";
                $record  = $trips->get($key);
                $labels[]      = $date->format('M Y');
                $tripData[]    = $record?->total_trips ?? 0;
                $revenueData[] = round($record?->total_revenue ?? 0, 2);
            }

            return [
                'labels'       => $labels,
                'trips'        => $tripData,
                'revenue'      => $revenueData,
            ];
        });
    }

    // ── Vehicle performance chart ──────────────────────
    public function getVehiclePerformanceChart(): array
    {
        return Cache::remember(
            "dashboard:vehicle-performance:{$this->tenantId}",
            now()->addMinutes(10),
            function () {
                $vehicles = Vehicle::withCount([
                    'trips as completed_trips' => fn($q) =>
                    $q->where('status', 'completed')
                        ->whereMonth('trip_date', now()->month)
                ])->get();

                return [
                    'labels'     => $vehicles->pluck('registration_number')->toArray(),
                    'trips'      => $vehicles->pluck('completed_trips')->toArray(),
                    'available'  => $vehicles->map(fn($v) => $v->is_available ? 1 : 0)->toArray(),
                ];
            }
        );
    }

    // ── Revenue by source chart ────────────────────────
    public function getRevenueBySourceChart(): array
    {
        return Cache::remember(
            "dashboard:revenue-source:{$this->tenantId}:" . now()->format('Y-m'),
            now()->addMinutes(10),
            function () {
                $tripRevenue      = Trip::whereMonth('trip_date', now()->month)->sum('total_amount');
                $corporateRevenue = DB::table('corporate_payments')
                    ->where('tenant_id', $this->tenantId)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount');

                return [
                    'labels' => ['Trip Revenue', 'Corporate Revenue'],
                    'data'   => [round($tripRevenue, 2), round($corporateRevenue, 2)],
                    'colors' => ['#1D9E75', '#534AB7'],
                ];
            }
        );
    }

    // ── Fuel efficiency chart ──────────────────────────
    public function getFuelEfficiencyChart(): array
    {
        return Cache::remember(
            "dashboard:fuel-efficiency:{$this->tenantId}",
            now()->addMinutes(15),
            function () {
                $logs = VehicleFuelLog::with('vehicle')
                    ->whereMonth('filled_on', now()->month)
                    ->whereNotNull('fuel_efficiency')
                    ->get()
                    ->groupBy('vehicle_id')
                    ->map(fn($logs) => [
                        'vehicle' => $logs->first()->vehicle?->registration_number,
                        'avg_efficiency' => round($logs->avg('fuel_efficiency'), 2),
                    ])
                    ->values();

                return [
                    'labels' => $logs->pluck('vehicle')->toArray(),
                    'data'   => $logs->pluck('avg_efficiency')->toArray(),
                ];
            }
        );
    }
}
