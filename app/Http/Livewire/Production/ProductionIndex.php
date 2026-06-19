<?php

namespace App\Http\Livewire\Production;

use App\Domains\Production\Models\ProductionOrder;
use App\Domains\Production\Services\ProductionService;
use App\Support\Enums\ProductionOrderStatus;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Órdenes de Producción')]
class ProductionIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    public bool $showTransitionModal = false;
    public ?int $orderIdToTransition = null;
    public string $targetStatus = '';
    public string $transitionNotes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openTransitionModal(int $orderId, string $status): void
    {
        $this->orderIdToTransition = $orderId;
        $this->targetStatus = $status;
        $this->transitionNotes = '';
        $this->showTransitionModal = true;
    }

    public function executeTransition(ProductionService $service): void
    {
        $order = ProductionOrder::findOrFail($this->orderIdToTransition);
        $newStatus = ProductionOrderStatus::from($this->targetStatus);

        try {
            $service->transitionStatus($order, $newStatus, $this->transitionNotes ?: null);
            $this->showTransitionModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Orden {$order->order_number} → {$newStatus->label()}",
            ]);
        } catch (\DomainException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $orders = ProductionOrder::with(['product', 'warehouse', 'assignedUser'])
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('order_number', 'ilike', "%{$this->search}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('created_at')
            ->paginate(25);

        $statusCounts = ProductionOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('livewire.production.index', [
            'orders' => $orders,
            'statuses' => ProductionOrderStatus::cases(),
            'statusCounts' => $statusCounts,
        ]);
    }
}
