<?php

namespace App\Http\Livewire\Sales;

use App\Domains\Sales\Models\Sale;
use App\Support\Enums\SaleStatus;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Ventas')]
class SaleIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public string $sortBy = 'sale_date';
    public string $sortDir = 'desc';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        $this->sortDir = ($this->sortBy === $field && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortBy = $field;
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'createdBy'])
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('order_number', 'ilike', "%{$this->search}%")
                    ->orWhere('invoice_number', 'ilike', "%{$this->search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('business_name', 'ilike', "%{$this->search}%")
                        ->orWhere('trade_name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        $statusCounts = Sale::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totals = Sale::selectRaw('
            COUNT(*) as total_orders,
            SUM(CASE WHEN status IN (\'confirmed\',\'invoiced\',\'paid\') THEN total ELSE 0 END) as confirmed_total,
            SUM(CASE WHEN status IN (\'confirmed\',\'invoiced\',\'paid\') THEN gross_profit ELSE 0 END) as total_profit
        ')->first();

        return view('livewire.sales.sale-index', [
            'sales' => $sales,
            'statuses' => SaleStatus::cases(),
            'statusCounts' => $statusCounts,
            'totals' => $totals,
        ]);
    }
}
