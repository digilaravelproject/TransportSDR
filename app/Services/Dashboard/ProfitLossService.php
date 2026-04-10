<?php

namespace App\Services\Dashboard;

use App\Models\{Trip, Vehicle};
use App\Models\{VehicleFuelLog, VehicleMaintenanceLog, VehicleLedger};
use Illuminate\Support\Facades\{Cache, DB};
use Carbon\CarbonPeriod;

class ProfitLossService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    // ── Overall P&L ────────────────────────────────────
    public function getOverallPL(string $from, string $to): array
    {
        $cacheKey = "dashboard:pl:{$this->tenantId}:{$from}:{$to}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($from, $to) {

            // Income
            $tripIncome      = Trip::whereBetween('trip_date', [$from, $to])
                ->where('payment_status', '!=', 'pending')
                ->sum('total_amount');

            $corporateIncome = DB::table('corporate_payments')
                ->where('tenant_id', $this->tenantId)
                ->whereBetween('billing_from', [$from, $to])
                ->sum('paid_amount');

            $totalIncome = $tripIncome + $corporateIncome;

            // Expenses
            $fuelExpense        = VehicleFuelLog::whereBetween('filled_on', [$from, $to])->sum('total_cost');
            $maintenanceExpense = VehicleMaintenanceLog::whereBetween('service_date', [$from, $to])->sum('total_cost');

            $staffExpense = DB::table('staff_salaries')
                ->where('tenant_id', $this->tenantId)
                ->whereBetween('paid_on', [$from, $to])
                ->where('payment_status', 'paid')
                ->sum('net_salary');

            $totalExpense = $fuelExpense + $maintenanceExpense + $staffExpense;

            $netPL         = $totalIncome - $totalExpense;
            $profitMargin  = $totalIncome > 0 ? round(($netPL / $totalIncome) * 100, 2) : 0;

            return [
                'period'         => ['from' => $from, 'to' => $to],
                'income'         => [
                    'trip_revenue'      => round($tripIncome, 2),
                    'corporate_revenue' => round($corporateIncome, 2),
                    'total'             => round($totalIncome, 2),
                ],
                'expense'        => [
                    'fuel'              => round($fuelExpense, 2),
                    'maintenance'       => round($maintenanceExpense, 2),
                    'staff_salary'      => round($staffExpense, 2),
                    'total'             => round($totalExpense, 2),
                ],
                'net_pl'         => round($netPL, 2),
                'profit_margin'  => $profitMargin,
                'is_profit'      => $netPL >= 0,
            ];
        });
    }

    // ── Vehicle wise P&L ───────────────────────────────
    public function getVehicleWisePL(string $from, string $to): array
    {
        $cacheKey = "dashboard:vehicle-pl:{$this->tenantId}:{$from}:{$to}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($from, $to) {
            $vehicles = Vehicle::get();
            $result   = [];

            foreach ($vehicles as $vehicle) {
                // Income from trips
                $income = Trip::where('vehicle_id', $vehicle->id)
                    ->whereBetween('trip_date', [$from, $to])
                    ->where('payment_status', '!=', 'pending')
                    ->sum('total_amount');

                // Expenses
                $fuelCost        = VehicleFuelLog::where('vehicle_id', $vehicle->id)->whereBetween('filled_on', [$from, $to])->sum('total_cost');
                $maintenanceCost = VehicleMaintenanceLog::where('vehicle_id', $vehicle->id)->whereBetween('service_date', [$from, $to])->sum('total_cost');

                $totalExpense = $fuelCost + $maintenanceCost;
                $netPL        = $income - $totalExpense;

                $result[] = [
                    'vehicle_id'           => $vehicle->id,
                    'registration_number'  => $vehicle->registration_number,
                    'type'                 => $vehicle->type,
                    'income'               => round($income, 2),
                    'fuel_expense'         => round($fuelCost, 2),
                    'maintenance_expense'  => round($maintenanceCost, 2),
                    'total_expense'        => round($totalExpense, 2),
                    'net_pl'               => round($netPL, 2),
                    'is_profit'            => $netPL >= 0,
                ];
            }

            // Sort by net P&L desc
            usort($result, fn($a, $b) => $b['net_pl'] <=> $a['net_pl']);

            return $result;
        });
    }

    // ── Monthly P&L trend chart ─────────────────────────
    public function getMonthlyPLTrend(int $months = 6): array
    {
        $cacheKey = "dashboard:pl-trend:{$this->tenantId}:{$months}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($months) {
            $labels   = [];
            $income   = [];
            $expense  = [];
            $netPL    = [];

            $start = now()->subMonths($months - 1)->startOfMonth();
            $period = CarbonPeriod::create($start, '1 month', now()->endOfMonth());

            foreach ($period as $date) {
                $from = $date->copy()->startOfMonth()->toDateString();
                $to   = $date->copy()->endOfMonth()->toDateString();
                $pl   = $this->getOverallPL($from, $to);

                $labels[]  = $date->format('M Y');
                $income[]  = $pl['income']['total'];
                $expense[] = $pl['expense']['total'];
                $netPL[]   = $pl['net_pl'];
            }

            return compact('labels', 'income', 'expense', 'netPL');
        });
    }

    // ── Trip wise P&L ──────────────────────────────────
    public function getTripWisePL(string $from, string $to, int $limit = 20): array
    {
        return Trip::with(['vehicle', 'customer'])
            ->whereBetween('trip_date', [$from, $to])
            ->where('status', 'completed')
            ->get()
            ->map(function ($trip) {
                $fuelCost = VehicleFuelLog::where('vehicle_id', $trip->vehicle_id)
                    ->whereDate('filled_on', $trip->trip_date)
                    ->sum('total_cost');

                $netPL = $trip->total_amount - $fuelCost;

                return [
                    'trip_number'   => $trip->trip_number,
                    'trip_date'     => $trip->trip_date->format('d-m-Y'),
                    'route'         => $trip->trip_route,
                    'customer'      => $trip->customer_name,
                    'vehicle'       => $trip->vehicle?->registration_number,
                    'revenue'       => round($trip->total_amount, 2),
                    'fuel_expense'  => round($fuelCost, 2),
                    'net_pl'        => round($netPL, 2),
                    'is_profit'     => $netPL >= 0,
                ];
            })
            ->sortByDesc('net_pl')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
