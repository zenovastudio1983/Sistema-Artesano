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
#[Title('Transferencia de Stock')]
class TransferStock extends Component
{
    public int $productId = 0;
    public int $sourceWarehouseId = 0;
    public int $destWarehouseId = 0;
    public string $quantity = '';
    public string $notes = '';
    public string $reference = '';

    protected function rules(): array
    {
        return [
            'productId'         => 'required|exists:products,id',
            'sourceWarehouseId' => 'required|exists:warehouses,id',
            'destWarehouseId'   => 'required|exists:warehouses,id|different:sourceWarehouseId',
            'quantity'          => 'required|numeric|min:0.0001',
            'notes'             => 'nullable|string|max:500',
            'reference'         => 'nullable|string|max:100',
        ];
    }

    protected $messages = [
        'destWarehouseId.different' => 'El almacén de destino debe ser diferente al de origen.',
    ];

    public function save(): void
    {
        $this->validate();

        $refNum = $this->reference ?: 'TRF-' . now()->format('YmdHis');

        StockMovement::create([
            'movement_type'             => MovementType::TransferOut,
            'product_id'                => $this->productId,
            'warehouse_id'              => $this->sourceWarehouseId,
            'destination_warehouse_id'  => $this->destWarehouseId,
            'quantity'                  => (float) $this->quantity,
            'reference_number'          => $refNum,
            'notes'                     => $this->notes ?: null,
            'created_by'                => auth()->id(),
            'moved_at'                  => now(),
        ]);

        StockMovement::create([
            'movement_type'             => MovementType::TransferIn,
            'product_id'                => $this->productId,
            'warehouse_id'              => $this->destWarehouseId,
            'destination_warehouse_id'  => null,
            'quantity'                  => (float) $this->quantity,
            'reference_number'          => $refNum,
            'notes'                     => $this->notes ?: null,
            'created_by'                => auth()->id(),
            'moved_at'                  => now(),
        ]);

        session()->flash('success', 'Transferencia registrada correctamente.');
        $this->redirect(route('inventory.movements'));
    }

    public function render()
    {
        return view('livewire.inventory.transfer-stock', [
            'products'   => Product::where('status', 'active')->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ]);
    }
}
