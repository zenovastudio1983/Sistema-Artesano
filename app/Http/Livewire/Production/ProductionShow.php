<?php

namespace App\Http\Livewire\Production;

use App\Domains\Production\Models\ProductionOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Producción')]
class ProductionShow extends Component
{
    public ProductionOrder $order;

    public function mount(ProductionOrder $order): void
    {
        $this->order = $order->load(['product', 'recipe.ingredients.product', 'warehouse', 'assignedUser', 'createdBy', 'materials.product']);
    }

    public function render()
    {
        return view('livewire.production.production-show');
    }
}
