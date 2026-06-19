<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Domains\Inventory\Services\StockService;

class StockAlertsBadge extends Component
{
    public int $criticalCount = 0;

    public function mount(StockService $stockService): void
    {
        try {
            $this->criticalCount = $stockService->getLowStockProducts()
                ->filter(fn($p) => $p->stock_minimum > 0)
                ->count();
        } catch (\Exception $e) {
            $this->criticalCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.stock-alerts-badge');
    }
}
