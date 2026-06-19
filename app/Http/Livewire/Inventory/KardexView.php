<?php

namespace App\Http\Livewire\Inventory;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Products\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Kardex de Producto')]
class KardexView extends Component
{
    public Product $product;
    public int $warehouseId;
    public string $dateFrom;
    public string $dateTo;

    public function mount(Product $product, StockService $stockService): void
    {
        $this->product = $product;
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->warehouseId = Warehouse::where('is_default', true)->first()?->id ?? 0;
    }

    public function render(StockService $stockService)
    {
        $warehouse = Warehouse::find($this->warehouseId);
        $movements = $warehouse
            ? $stockService->getKardex($this->product, $warehouse, $this->dateFrom, $this->dateTo)
            : collect();

        $currentInventory = $warehouse
            ? \App\Domains\Inventory\Models\Inventory::where('product_id', $this->product->id)
                ->where('warehouse_id', $this->warehouseId)
                ->first()
            : null;

        return view('livewire.inventory.kardex', [
            'movements' => $movements,
            'warehouses' => Warehouse::active()->get(),
            'currentInventory' => $currentInventory,
        ]);
    }
}
