<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Support\Enums\MovementType;
use App\Support\Enums\PurchaseOrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Recepción de Mercancía')]
class ReceiveGoods extends Component
{
    public PurchaseOrder $order;
    public array $receiving = [];
    public string $receivedDate = '';
    public string $notes = '';

    public function mount(PurchaseOrder $order): void
    {
        $this->order = $order->load(['supplier', 'warehouse', 'items.product']);
        $this->receivedDate = now()->toDateString();

        foreach ($this->order->items as $item) {
            $pending = max(0, (float) $item->quantity - (float) $item->received_quantity);
            $this->receiving[$item->id] = [
                'product_name' => $item->product->name ?? 'Producto',
                'ordered'      => (float) $item->quantity,
                'received'     => (float) $item->received_quantity,
                'pending'      => $pending,
                'now'          => $pending,
                'unit_cost'    => (float) $item->unit_price,
            ];
        }
    }

    public function save(): void
    {
        $this->validate([
            'receivedDate' => 'required|date',
        ]);

        $hasReceived = false;

        foreach ($this->receiving as $itemId => $rec) {
            $qty = (float) ($rec['now'] ?? 0);
            if ($qty <= 0) continue;

            $item = $this->order->items->firstWhere('id', $itemId);
            if (!$item) continue;

            $item->increment('received_quantity', $qty);

            StockMovement::create([
                'movement_type'    => MovementType::PurchaseEntry,
                'product_id'       => $item->product_id,
                'warehouse_id'     => $this->order->warehouse_id,
                'quantity'         => $qty,
                'unit_cost'        => (float) $rec['unit_cost'],
                'reference_number' => $this->order->order_number,
                'notes'            => $this->notes ?: "Recepción OC {$this->order->order_number}",
                'created_by'       => auth()->id(),
                'moved_at'         => now(),
            ]);

            $hasReceived = true;
        }

        if (!$hasReceived) {
            $this->addError('receiving', 'Ingresa al menos una cantidad a recibir.');
            return;
        }

        $order = $this->order->fresh(['items']);
        $allReceived = $order->items->every(fn($i) => (float) $i->received_quantity >= (float) $i->quantity);

        $newStatus = $allReceived ? PurchaseOrderStatus::Received : PurchaseOrderStatus::PartiallyReceived;
        $order->update(['status' => $newStatus, 'received_date' => $this->receivedDate]);

        session()->flash('success', 'Recepción registrada correctamente.');
        $this->redirect(route('purchases.show', $this->order));
    }

    public function render()
    {
        return view('livewire.purchases.receive-goods');
    }
}
