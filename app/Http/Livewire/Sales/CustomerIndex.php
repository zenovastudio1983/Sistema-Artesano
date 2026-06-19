<?php

namespace App\Http\Livewire\Sales;

use App\Domains\Sales\Models\Customer;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Clientes')]
class CustomerIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    #[Url(except: '')]
    public string $filterType = '';

    public string $sortBy = 'business_name';
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }

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
        Customer::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Cliente eliminado.');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filterStatus === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->filterType, fn($q) => $q->where('customer_type', $this->filterType))
            ->withCount('sales')
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        return view('livewire.sales.customer-index', compact('customers'));
    }
}
