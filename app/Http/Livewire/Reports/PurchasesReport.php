<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use App\Domains\Purchases\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reporte de Compras')]
class PurchasesReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    #[Url(except: 0)]
    public int $supplierId = 0;

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
        $data = $service->getPurchasesReport(
            $this->dateFrom,
            $this->dateTo,
            $this->supplierId ?: null,
        );

        return view('livewire.reports.purchases-report', [
            'report' => $data,
            'suppliers' => Supplier::orderBy('business_name')->get(),
        ]);
    }
}
