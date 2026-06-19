<?php

namespace App\Jobs;

use App\Domains\Reports\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        private string $reportType,
        private array $params,
        private int $userId,
        private string $format = 'xlsx'
    ) {
        $this->onQueue('reports');
    }

    public function handle(ReportService $reportService): void
    {
        $data = match($this->reportType) {
            'sales' => $reportService->getSalesReport(
                $this->params['from'],
                $this->params['to'],
                $this->params['customer_id'] ?? null,
                $this->params['group_by'] ?? 'day'
            ),
            'purchases' => $reportService->getPurchasesReport(
                $this->params['from'],
                $this->params['to'],
                $this->params['supplier_id'] ?? null
            ),
            'inventory' => $reportService->getInventoryReport(),
            'production_costs' => $reportService->getProductionCostReport(
                $this->params['from'],
                $this->params['to']
            ),
            default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };

        $cacheKey = "report.{$this->reportType}.{$this->userId}." . md5(json_encode($this->params));
        Cache::put($cacheKey, $data, now()->addHours(2));
    }
}
