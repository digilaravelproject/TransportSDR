<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Dashboard\{
    DashboardService,
    SummaryService,
    ChartService,
    ProfitLossService,
    PerformanceService,
    NotificationService
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardService::class, function ($app) {
            return new DashboardService(
                $app->make(SummaryService::class),
                $app->make(ChartService::class),
                $app->make(ProfitLossService::class),
                $app->make(PerformanceService::class),
                $app->make(NotificationService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
