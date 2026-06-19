<?php

namespace App\Http\Livewire\Inventory;

use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Inventory\Models\Warehouse;
use App\Support\Enums\MovementType;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Movimientos de Inventario')]
class MovementIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterType = '';

    #[Url(except: 0)]
    public int $filterWarehouse = 0;

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }
    public function updatedFilterWarehouse(): void { $this->resetPage(); }

    public function render()
    {
        $movements = StockMovement::with(['product', 'warehouse', 'createdBy'])
            ->when($this->search, fn($q) => $q->whereHas('product', fn($p) =>
                $p->where('name', 'ilike', "%{$this->search}%")
                  ->orWhere('sku', 'ilike', "%{$this->search}%")
            ))
            ->when($this->filterType, fn($q) => $q->where('movement_type', $this->filterType))
            ->when($this->filterWarehouse, fn($q) => $q->where('warehouse_id', $this->filterWarehouse))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('livewire.inventory.movement-index', [
            'movements' => $movements,
            'warehouses' => Warehouse::active()->get(),
            'movementTypes' => MovementType::cases(),
        ]);
    }
}
