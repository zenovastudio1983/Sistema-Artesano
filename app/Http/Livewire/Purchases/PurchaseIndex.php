<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Purchases\Models\PurchaseOrder;
use App\Support\Enums\PurchaseOrderStatus;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Órdenes de Compra')]
class PurchaseIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

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
        $order = PurchaseOrder::findOrFail($this->deleteId);
        if ($order->status === PurchaseOrderStatus::Draft) {
            $order->delete();
            session()->flash('success', 'Orden eliminada.');
        } else {
            session()->flash('error', 'Solo se pueden eliminar órdenes en borrador.');
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse', 'createdBy'])
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('order_number', 'ilike', "%{$this->search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('business_name', 'ilike', "%{$this->search}%")
                        ->orWhere('trade_name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        $statusCounts = PurchaseOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('livewire.purchases.purchase-index', [
            'orders' => $orders,
            'statuses' => PurchaseOrderStatus::cases(),
            'statusCounts' => $statusCounts,
        ]);
    }
}
