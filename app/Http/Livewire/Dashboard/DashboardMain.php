<?php

namespace App\Http\Livewire\Dashboard;

use App\Domains\Inventory\Services\StockService;
use App\Domains\Reports\Services\ReportService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Dashboard Ejecutivo')]
class DashboardMain extends Component
{
    public array $kpis = [];
    public array $lowStockProducts = [];
    public string $salesPeriod = 'month';

    public function mount(ReportService $reportService, StockService $stockService): void
    {
        $this->kpis = $reportService->getDashboardKpis();
        $this->lowStockProducts = $stockService->getLowStockProducts()
            ->take(5)
            ->toArray();
    }

    public function refreshKpis(ReportService $reportService): void
    {
        $reportService->invalidateCache();
        $this->kpis = $reportService->getDashboardKpis();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'KPIs actualizados',
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.main');
    }
}
