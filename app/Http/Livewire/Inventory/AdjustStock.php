<?php

namespace App\Http\Livewire\Inventory;

use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Products\Models\Product;
use App\Support\Enums\MovementType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ajuste de Stock')]
class AdjustStock extends Component
{
    public int $productId = 0;
    public int $warehouseId = 0;
    public string $adjustmentType = 'positive';
    public string $quantity = '';
    public string $unitCost = '';
    public string $notes = '';
    public string $reference = '';

    protected function rules(): array
    {
        return [
            'productId'      => 'required|exists:products,id',
            'warehouseId'    => 'required|exists:warehouses,id',
            'adjustmentType' => 'required|in:positive,negative',
            'quantity'       => 'required|numeric|min:0.0001',
            'unitCost'       => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
            'reference'      => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $movementType = $this->adjustmentType === 'positive'
            ? MovementType::PositiveAdjustment
            : MovementType::NegativeAdjustment;

        StockMovement::create([
            'movement_type'    => $movementType,
            'product_id'       => $this->productId,
            'warehouse_id'     => $this->warehouseId,
            'quantity'         => abs((float) $this->quantity),
            'unit_cost'        => $this->unitCost ? (float) $this->unitCost : null,
            'reference_number' => $this->reference ?: null,
            'notes'            => $this->notes ?: null,
            'created_by'       => auth()->id(),
            'moved_at'         => now(),
        ]);

        session()->flash('success', 'Ajuste registrado correctamente.');
        $this->redirect(route('inventory.movements'));
    }

    public function render()
    {
        return view('livewire.inventory.adjust-stock', [
            'products'   => Product::where('status', 'active')->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ]);
    }
}
