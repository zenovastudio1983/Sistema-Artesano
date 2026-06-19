<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reporte de Producción')]
class ProductionReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        if (!$this->dateFrom) {
            $this->dateFrom = now()->startOfYear()->toDateString();
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->toDateString();
        }
    }

    public function render(ReportService $service)
    {
        $data = $service->getProductionCostReport($this->dateFrom, $this->dateTo);

        return view('livewire.reports.production-report', [
            'report' => $data,
        ]);
    }
}
