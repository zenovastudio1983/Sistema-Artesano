<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Purchases\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Proveedores')]
class SupplierIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    public string $sortBy = 'business_name';
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        $this->sortDir = ($this->sortBy === $field && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortBy = $field;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Supplier::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Proveedor eliminado.');
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filterStatus === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->withCount('purchaseOrders')
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        return view('livewire.purchases.supplier-index', compact('suppliers'));
    }
}
