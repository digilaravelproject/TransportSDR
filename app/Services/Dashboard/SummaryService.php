<?php

namespace App\Services\Dashboard;

use App\Models\{Trip, Lead, Vehicle, Staff, Customer, Corporate};
use App\Models\{TripPayment, VehicleFuelLog, VehicleMaintenanceLog};
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SummaryService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    public function getKpis(): array
    {
        $cacheKey = "dashboard:kpis:{$this->tenantId}:" . today()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {

            $today     = today();
            $thisMonth = now()->startOfMonth();

            return [
                'trips' => [
                    'total'         => Trip::count(),
                    'today'         => Trip::whereDate('trip_date', $today)->count(),
                    'this_month'    => Trip::whereDate('trip_date', '>=', $thisMonth)->count(),
                    'scheduled'     => Trip::where('status', 'scheduled')->count(),
                    'ongoing'       => Trip::where('status', 'ongoing')->count(),
                    'completed'     => Trip::where('status', 'completed')->count(),
                    'cancelled'     => Trip::where('status', 'cancelled')->count(),
                ],

                'leads' => [
                    'total'         => Lead::count(),
                    'new'           => Lead::where('status', 'new')->count(),
                    'followup_today' => Lead::whereDate('followup_date', $today)->count(),
                    'converted'     => Lead::where('status', 'converted')->count(),
                    'lost'          => Lead::where('status', 'lost')->count(),
                    'conversion_rate' => $this->getLeadConversionRate(),
                ],

                'vehicles' => [
                    'total'         => Vehicle::count(),
                    'available'     => Vehicle::where('is_available', true)->count(),
                    'on_trip'       => Vehicle::where('is_available', false)->count(),
                    'inactive'      => Vehicle::where('is_active', false)->count(),
                ],

                'staff' => [
                    'total'         => Staff::count(),
                    'drivers'       => Staff::where('staff_type', 'driver')->count(),
                    'helpers'       => Staff::where('staff_type', 'helper')->count(),
                    'available'     => Staff::where('is_available', true)->count(),
                ],

                'revenue' => [
                    'today'         => $this->getRevenue('today'),
                    'this_week'     => $this->getRevenue('week'),
                    'this_month'    => $this->getRevenue('month'),
                    'this_year'     => $this->getRevenue('year'),
                    'pending_balance' => Trip::where('payment_status', '!=', 'paid')->sum('balance_amount'),
                ],

                'customers' => [
                    'total'         => Customer::count(),
                    'this_month'    => Customer::whereDate('created_at', '>=', $thisMonth)->count(),
                ],

                'corporate' => [
                    'total'         => Corporate::count(),
                    'active'        => Corporate::where('is_active', true)->count(),
                ],
            ];
        });
    }

    private function getLeadConversionRate(): float
    {
        $total     = Lead::count();
        $converted = Lead::where('status', 'converted')->count();

        if ($total === 0) return 0.0;

        return round(($converted / $total) * 100, 2);
    }

    private function getRevenue(string $period): float
    {
        $query = Trip::where('payment_status', '!=', 'pending');

        return (float) match ($period) {
            'today'  => $query->whereDate('trip_date', today())->sum('total_amount'),
            'week'   => $query->whereBetween('trip_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'month'  => $query->whereMonth('trip_date', now()->month)->whereYear('trip_date', now()->year)->sum('total_amount'),
            'year'   => $query->whereYear('trip_date', now()->year)->sum('total_amount'),
            default  => 0,
        };
    }
}
