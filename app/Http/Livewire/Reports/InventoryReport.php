<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reporte de Inventario')]
class InventoryReport extends Component
{
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    public function render(ReportService $service)
    {
        $data = $service->getInventoryReport();

        $items = collect($data['items']);

        if ($this->search) {
            $items = $items->filter(fn($i) =>
                str_contains(strtolower($i->product_name ?? ''), strtolower($this->search)) ||
                str_contains(strtolower($i->sku ?? ''), strtolower($this->search))
            );
        }

        if ($this->filterStatus) {
            $items = $items->where('stock_status', $this->filterStatus);
        }

        return view('livewire.reports.inventory-report', [
            'items' => $items->values(),
            'summary' => $data['summary'],
        ]);
    }
}
