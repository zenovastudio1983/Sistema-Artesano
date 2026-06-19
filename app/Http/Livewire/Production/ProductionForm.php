<?php

namespace App\Http\Livewire\Production;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Production\Models\ProductionOrder;
use App\Domains\Recipes\Models\Recipe;
use App\Domains\Users\Models\User;
use App\Support\Enums\ProductionOrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Producción')]
class ProductionForm extends Component
{
    public ?int $orderId = null;

    public int $recipeId = 0;
    public int $warehouseId = 0;
    public int $assignedTo = 0;
    public string $plannedQuantity = '';
    public string $plannedStartDate = '';
    public string $plannedEndDate = '';
    public string $notes = '';

    public ?Recipe $selectedRecipe = null;
    public float $estimatedMaterialCost = 0;
    public float $estimatedTotalCost = 0;

    public function mount(?ProductionOrder $order = null): void
    {
        $this->plannedStartDate = now()->toDateString();
        $this->plannedEndDate   = now()->addDays(3)->toDateString();

        if ($order && $order->exists) {
            $this->orderId          = $order->id;
            $this->recipeId         = $order->recipe_id ?? 0;
            $this->warehouseId      = $order->warehouse_id ?? 0;
            $this->assignedTo       = $order->assigned_to ?? 0;
            $this->plannedQuantity  = (string) $order->planned_quantity;
            $this->plannedStartDate = $order->planned_start_date?->toDateString() ?? now()->toDateString();
            $this->plannedEndDate   = $order->planned_end_date?->toDateString() ?? '';
            $this->notes            = $order->notes ?? '';

            if ($this->recipeId) {
                $this->selectedRecipe = Recipe::find($this->recipeId);
                $this->recalculate();
            }
        }
    }

    public function updatedRecipeId(int $value): void
    {
        if ($value) {
            $this->selectedRecipe = Recipe::with('product')->find($value);
            $this->recalculate();
        } else {
            $this->selectedRecipe = null;
            $this->estimatedMaterialCost = 0;
            $this->estimatedTotalCost = 0;
        }
    }

    public function updatedPlannedQuantity(): void
    {
        $this->recalculate();
    }

    private function recalculate(): void
    {
        if (!$this->selectedRecipe || !$this->plannedQuantity) return;

        $recipe = $this->selectedRecipe;
        $qty    = (float) $this->plannedQuantity;
        $yield  = (float) $recipe->yield_quantity;

        if ($yield > 0) {
            $factor = $qty / $yield;
            $this->estimatedMaterialCost = round((float) $recipe->material_cost * $factor, 4);
            $this->estimatedTotalCost    = round((float) $recipe->total_cost * $factor, 4);
        }
    }

    public function save(): void
    {
        $this->validate([
            'recipeId'        => 'required|exists:recipes,id',
            'warehouseId'     => 'required|exists:warehouses,id',
            'plannedQuantity' => 'required|numeric|min:0.0001',
            'plannedStartDate' => 'required|date',
        ]);

        $recipe  = Recipe::findOrFail($this->recipeId);
        $this->recalculate();

        $data = [
            'recipe_id'                  => $this->recipeId,
            'product_id'                 => $recipe->product_id,
            'warehouse_id'               => $this->warehouseId,
            'assigned_to'                => $this->assignedTo ?: null,
            'planned_quantity'           => (float) $this->plannedQuantity,
            'planned_start_date'         => $this->plannedStartDate,
            'planned_end_date'           => $this->plannedEndDate ?: null,
            'estimated_material_cost'    => $this->estimatedMaterialCost,
            'estimated_labor_cost'       => round((float) $recipe->labor_cost * (float) $this->plannedQuantity / max(1, (float) $recipe->yield_quantity), 4),
            'estimated_overhead_cost'    => round((float) $recipe->overhead_cost * (float) $this->plannedQuantity / max(1, (float) $recipe->yield_quantity), 4),
            'estimated_total_cost'       => $this->estimatedTotalCost,
            'notes'                      => $this->notes ?: null,
        ];

        if ($this->orderId) {
            ProductionOrder::findOrFail($this->orderId)->update($data);
            $message = 'Orden actualizada.';
            $orderId = $this->orderId;
        } else {
            $data['status']     = ProductionOrderStatus::Planned;
            $data['created_by'] = auth()->id();
            $order = ProductionOrder::create($data);
            $orderId = $order->id;
            $message = 'Orden de producción creada.';
        }

        session()->flash('success', $message);
        $this->redirect(route('production.show', $orderId));
    }

    public function render()
    {
        return view('livewire.production.production-form', [
            'recipes'    => Recipe::active()->with('product')->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'users'      => User::active()->orderBy('name')->get(),
        ]);
    }
}
