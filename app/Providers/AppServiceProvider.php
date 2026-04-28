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

use App\Services\Template\{
    TemplateService,
    InvoiceService,
    LetterheadService,
    QuotationService,
    EInvoiceService
};

use App\Services\CashBook\{CashBookService, LedgerService, OnlinePaymentService, QrService};

// inventory services removed - inventory module replaced by new implementation

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
        $this->app->bind(TemplateService::class, function ($app) {
            return new TemplateService(
                $app->make(InvoiceService::class),
                $app->make(LetterheadService::class),
                $app->make(QuotationService::class),
                $app->make(EInvoiceService::class),
            );
        });

        $this->app->bind(CashBookService::class, function ($app) {
            $ledger = $app->make(LedgerService::class);
            return new CashBookService(
                $ledger,
                new OnlinePaymentService($ledger),
                $app->make(QrService::class),
            );
        });

        $this->app->bind(InventoryService::class, function ($app) {
            return new InventoryService(
                $app->make(StockService::class),
                $app->make(AlertService::class),
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
