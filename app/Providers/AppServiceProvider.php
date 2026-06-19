<?php

namespace App\Providers;

use App\Domains\Inventory\Services\StockService;
use App\Domains\Production\Services\ProductionService;
use App\Domains\Purchases\Services\PurchaseService;
use App\Domains\Recipes\Services\RecipeService;
use App\Domains\Reports\Services\ReportService;
use App\Domains\Sales\Services\SaleService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StockService::class);
        $this->app->singleton(RecipeService::class);

        $this->app->singleton(ProductionService::class, function ($app) {
            return new ProductionService(
                $app->make(StockService::class),
                $app->make(RecipeService::class)
            );
        });

        $this->app->singleton(PurchaseService::class, function ($app) {
            return new PurchaseService($app->make(StockService::class));
        });

        $this->app->singleton(SaleService::class, function ($app) {
            return new SaleService($app->make(StockService::class));
        });

        $this->app->singleton(ReportService::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
        Model::unguard();

        if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 1000) {
                    \Illuminate\Support\Facades\Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'time' => $query->time . 'ms',
                    ]);
                }
            });
        }
    }
}
