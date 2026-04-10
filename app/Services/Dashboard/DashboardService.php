<?php

namespace App\Services\Dashboard;

class DashboardService
{
    public function __construct(
        private SummaryService     $summary,
        private ChartService       $chart,
        private ProfitLossService  $profitLoss,
        private PerformanceService $performance,
        private NotificationService $notification,
    ) {}

    public function getSummary(): array
    {
        return $this->summary->getKpis();
    }

    public function getCharts(): array
    {
        return [
            'trip_status'      => $this->chart->getTripStatusChart(),
            'lead_funnel'      => $this->chart->getLeadFunnelChart(),
            'monthly_trend'    => $this->chart->getMonthlyTripRevenueChart(),
            'vehicle_perf'     => $this->chart->getVehiclePerformanceChart(),
            'revenue_source'   => $this->chart->getRevenueBySourceChart(),
            'fuel_efficiency'  => $this->chart->getFuelEfficiencyChart(),
        ];
    }

    public function getProfitLoss(string $from, string $to): array
    {
        return [
            'overall'       => $this->profitLoss->getOverallPL($from, $to),
            'vehicle_wise'  => $this->profitLoss->getVehicleWisePL($from, $to),
            'trip_wise'     => $this->profitLoss->getTripWisePL($from, $to),
            'monthly_trend' => $this->profitLoss->getMonthlyPLTrend(),
        ];
    }

    public function getPerformance(string $period): array
    {
        return $this->performance->get($period);
    }

    public function getNotifications(): array
    {
        return $this->notification->getAllNotifications();
    }
}
