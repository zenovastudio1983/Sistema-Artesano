<?php

namespace App\Http\Livewire\Sales;

use App\Domains\Sales\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Venta')]
class SaleShow extends Component
{
    public Sale $sale;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load(['customer', 'warehouse', 'items.product', 'createdBy', 'seller']);
    }

    public function render()
    {
        return view('livewire.sales.sale-show');
    }
}
