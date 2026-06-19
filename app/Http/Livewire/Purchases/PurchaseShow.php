<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Purchases\Models\PurchaseOrder;
use App\Support\Enums\PurchaseOrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Compra')]
class PurchaseShow extends Component
{
    public PurchaseOrder $order;

    public function mount(PurchaseOrder $order): void
    {
        $this->order = $order->load(['supplier', 'warehouse', 'items.product', 'createdBy', 'approvedBy']);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-show');
    }
}
