<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('Reporte de Rentabilidad')]
class ProfitabilityReport extends Component
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
        $salesData = $service->getSalesReport($this->dateFrom, $this->dateTo, null, 'month');

        $byProduct = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereIn('sales.status', ['confirmed', 'invoiced', 'paid'])
            ->whereBetween('sales.sale_date', [$this->dateFrom, $this->dateTo])
            ->whereNull('sales.deleted_at')
            ->selectRaw('
                products.id, products.sku, products.name, products.unit,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.subtotal) as total_revenue,
                SUM(sale_items.margin) as total_margin,
                CASE WHEN SUM(sale_items.subtotal) > 0
                    THEN ROUND(SUM(sale_items.margin) / SUM(sale_items.subtotal) * 100, 2)
                    ELSE 0 END as margin_pct
            ')
            ->groupBy('products.id', 'products.sku', 'products.name', 'products.unit')
            ->orderByDesc('total_margin')
            ->get();

        return view('livewire.reports.profitability-report', [
            'salesData' => $salesData,
            'byProduct' => $byProduct,
        ]);
    }
}
