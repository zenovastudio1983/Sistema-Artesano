<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use App\Domains\Sales\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reporte de Ventas')]
class SalesReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    #[Url(except: 0)]
    public int $customerId = 0;

    #[Url(except: 'month')]
    public string $groupBy = 'month';

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
        $data = $service->getSalesReport(
            $this->dateFrom,
            $this->dateTo,
            $this->customerId ?: null,
            $this->groupBy,
        );

        return view('livewire.reports.sales-report', [
            'report' => $data,
            'customers' => Customer::orderBy('business_name')->get(),
        ]);
    }
}
