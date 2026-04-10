<?php

namespace App\Services\Dashboard;

use App\Models\{Trip, Lead, Vehicle};
use Illuminate\Support\Facades\{Cache, DB};
use Carbon\Carbon;

class PerformanceService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    public function get(string $period): array
    {
        [$from, $to, $groupBy, $format] = $this->resolvePeriod($period);

        $cacheKey = "dashboard:performance:{$this->tenantId}:{$period}:" . today()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($from, $to, $groupBy, $format, $period) {
            return [
                'period'        => $period,
                'from'          => $from,
                'to'            => $to,
                'trips'         => $this->getTripPerformance($from, $to, $groupBy, $format),
                'leads'         => $this->getLeadPerformance($from, $to, $groupBy, $format),
                'revenue'       => $this->getRevenuePerformance($from, $to, $groupBy, $format),
                'comparisons'   => $this->getComparisons($period),
            ];
        });
    }

    private function getTripPerformance(string $from, string $to, string $groupBy, string $format): array
    {
        $rows = Trip::whereBetween('trip_date', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(trip_date, '{$format}') as label"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status = 'completed') as completed"),
                DB::raw("SUM(status = 'cancelled') as cancelled"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels'    => $rows->pluck('label')->toArray(),
            'total'     => $rows->pluck('total')->toArray(),
            'completed' => $rows->pluck('completed')->toArray(),
            'cancelled' => $rows->pluck('cancelled')->toArray(),
            'revenue'   => $rows->map(fn($r) => round($r->revenue, 2))->toArray(),
        ];
    }

    private function getLeadPerformance(string $from, string $to, string $groupBy, string $format): array
    {
        $rows = Lead::whereBetween('enquiry_date', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(enquiry_date, '{$format}') as label"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status = 'converted') as converted"),
                DB::raw("SUM(status = 'lost') as lost")
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels'    => $rows->pluck('label')->toArray(),
            'total'     => $rows->pluck('total')->toArray(),
            'converted' => $rows->pluck('converted')->toArray(),
            'lost'      => $rows->pluck('lost')->toArray(),
        ];
    }

    private function getRevenuePerformance(string $from, string $to, string $groupBy, string $format): array
    {
        $rows = Trip::whereBetween('trip_date', [$from, $to])
            ->where('payment_status', '!=', 'pending')
            ->select(
                DB::raw("DATE_FORMAT(trip_date, '{$format}') as label"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(advance_amount) as collected'),
                DB::raw('SUM(balance_amount) as pending')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels'    => $rows->pluck('label')->toArray(),
            'revenue'   => $rows->map(fn($r) => round($r->revenue, 2))->toArray(),
            'collected' => $rows->map(fn($r) => round($r->collected, 2))->toArray(),
            'pending'   => $rows->map(fn($r) => round($r->pending, 2))->toArray(),
        ];
    }

    private function getComparisons(string $period): array
    {
        [$from, $to]         = $this->resolvePeriod($period);
        [$prevFrom, $prevTo] = $this->getPreviousPeriod($period);

        $currentTrips   = Trip::whereBetween('trip_date', [$from, $to])->count();
        $previousTrips  = Trip::whereBetween('trip_date', [$prevFrom, $prevTo])->count();

        $currentRevenue  = Trip::whereBetween('trip_date', [$from, $to])->sum('total_amount');
        $previousRevenue = Trip::whereBetween('trip_date', [$prevFrom, $prevTo])->sum('total_amount');

        return [
            'trips' => [
                'current'    => $currentTrips,
                'previous'   => $previousTrips,
                'change'     => $this->calculateChange($currentTrips, $previousTrips),
                'trend'      => $currentTrips >= $previousTrips ? 'up' : 'down',
            ],
            'revenue' => [
                'current'    => round($currentRevenue, 2),
                'previous'   => round($previousRevenue, 2),
                'change'     => $this->calculateChange($currentRevenue, $previousRevenue),
                'trend'      => $currentRevenue >= $previousRevenue ? 'up' : 'down',
            ],
        ];
    }

    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function resolvePeriod(string $period): array
    {
        return match ($period) {
            'today'  => [today()->toDateString(), today()->toDateString(), 'hour', '%H:00'],
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString(), 'day', '%d %b'],
            'month'  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), 'day', '%d %b'],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString(), 'month', '%b %Y'],
            default  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), 'day', '%d %b'],
        };
    }

    private function getPreviousPeriod(string $period): array
    {
        return match ($period) {
            'today'  => [yesterday()->toDateString(), yesterday()->toDateString()],
            'week'   => [now()->subWeek()->startOfWeek()->toDateString(), now()->subWeek()->endOfWeek()->toDateString()],
            'month'  => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            'year'   => [now()->subYear()->startOfYear()->toDateString(), now()->subYear()->endOfYear()->toDateString()],
            default  => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
        };
    }
}
