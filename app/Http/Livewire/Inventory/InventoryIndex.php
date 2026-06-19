<?php

namespace App\Http\Livewire\Inventory;

use App\Domains\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Inventario')]
class InventoryIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    #[Url(except: '')]
    public string $filterType = '';

    #[Url(except: 0)]
    public int $filterWarehouse = 0;

    public string $sortBy = 'product_name';
    public string $sortDir = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DB::table('v_inventory_status')
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('product_name', 'ilike', "%{$this->search}%")
                        ->orWhere('sku', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('stock_status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('product_type', $this->filterType))
            ->where('product_status', 'active')
            ->orderBy($this->sortBy, $this->sortDir);

        $inventory = $query->paginate(25);

        $summary = DB::table('v_inventory_status')
            ->where('product_status', 'active')
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(total_inventory_value) as total_value,
                COUNT(CASE WHEN stock_status = \'out_of_stock\' THEN 1 END) as out_of_stock,
                COUNT(CASE WHEN stock_status = \'critical\' THEN 1 END) as critical,
                COUNT(CASE WHEN stock_status = \'low\' THEN 1 END) as low
            ')
            ->first();

        return view('livewire.inventory.index', [
            'inventory' => $inventory,
            'summary' => $summary,
            'warehouses' => Warehouse::active()->get(),
        ]);
    }
}
