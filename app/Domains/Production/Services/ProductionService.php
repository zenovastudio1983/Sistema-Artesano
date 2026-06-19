<?php

namespace App\Domains\Production\Services;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Production\Models\ProductionOrder;
use App\Domains\Production\Models\ProductionOrderLog;
use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Models\Recipe;
use App\Domains\Recipes\Services\RecipeService;
use App\Support\Enums\MovementType;
use App\Support\Enums\ProductionOrderStatus;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    public function __construct(
        private StockService $stockService,
        private RecipeService $recipeService
    ) {}

    public function create(array $data): ProductionOrder
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $recipe = isset($data['recipe_id'])
                ? Recipe::findOrFail($data['recipe_id'])
                : $product->defaultRecipe;

            if (!$recipe) {
                throw new \DomainException("El producto '{$product->name}' no tiene receta activa configurada.");
            }

            $order = ProductionOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'status' => ProductionOrderStatus::Draft,
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'warehouse_id' => $data['warehouse_id'],
                'planned_quantity' => $data['planned_quantity'],
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createMaterials($order, $recipe);
            $this->calculateEstimatedCosts($order, $recipe);

            return $order->fresh(['product', 'recipe', 'materials.product']);
        });
    }

    public function transitionStatus(ProductionOrder $order, ProductionOrderStatus $newStatus, ?string $notes = null): ProductionOrder
    {
        if (!$order->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "No se puede cambiar el estado de '{$order->status->label()}' a '{$newStatus->label()}'."
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $notes) {
            $fromStatus = $order->status;

            match ($newStatus) {
                ProductionOrderStatus::Planned => $this->onPlan($order),
                ProductionOrderStatus::InProgress => $this->onStart($order),
                ProductionOrderStatus::Finished => $this->onFinish($order),
                ProductionOrderStatus::Cancelled => $this->onCancel($order),
                default => null,
            };

            $order->update(['status' => $newStatus]);

            ProductionOrderLog::create([
                'production_order_id' => $order->id,
                'from_status' => $fromStatus->value,
                'to_status' => $newStatus->value,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return $order->fresh();
        });
    }

    public function registerProduction(ProductionOrder $order, float $producedQuantity, float $rejectedQuantity = 0): ProductionOrder
    {
        if ($order->status !== ProductionOrderStatus::InProgress) {
            throw new \DomainException('Solo se puede registrar producción en órdenes "En Proceso".');
        }

        if ($producedQuantity <= 0) {
            throw new \DomainException('La cantidad producida debe ser mayor a 0.');
        }

        return DB::transaction(function () use ($order, $producedQuantity, $rejectedQuantity) {
            $warehouse = Warehouse::findOrFail($order->warehouse_id);
            $product = $order->product;

            $unitCost = $order->estimated_total_cost > 0
                ? $order->estimated_total_cost / $order->planned_quantity
                : ($product->average_cost ?? 0);

            $this->stockService->recordMovement(
                $product,
                $warehouse,
                $producedQuantity,
                MovementType::ProductionEntry,
                $unitCost,
                $order->order_number,
                "Ingreso por Orden de Producción {$order->order_number}",
                auth()->id(),
                $order
            );

            $order->increment('produced_quantity', $producedQuantity);
            $order->increment('rejected_quantity', $rejectedQuantity);

            $actualTotal = $order->materials->sum('total_cost');
            $order->update([
                'actual_material_cost' => $actualTotal,
                'actual_total_cost' => $actualTotal + $order->actual_labor_cost + $order->actual_overhead_cost,
                'unit_cost' => $order->produced_quantity > 0
                    ? ($actualTotal / $order->produced_quantity)
                    : 0,
            ]);

            return $order->fresh();
        });
    }

    private function onPlan(ProductionOrder $order): void
    {
        // Verificar disponibilidad de materiales
        $availability = $this->recipeService->checkMaterialAvailability(
            $order->recipe,
            $order->planned_quantity,
            $order->warehouse_id
        );

        $insufficient = array_filter($availability, fn($a) => !$a['sufficient']);
        if (!empty($insufficient)) {
            $warnings = array_map(
                fn($a) => "{$a['product']->name}: necesita {$a['needed']} {$a['product']->unit}, disponible {$a['available']}",
                $insufficient
            );
            // Advertencia, no bloquea
        }
    }

    private function onStart(ProductionOrder $order): void
    {
        $warehouse = Warehouse::findOrFail($order->warehouse_id);
        $order->update(['started_at' => now()]);

        // Consumir materiales del inventario
        foreach ($order->materials as $material) {
            $this->stockService->recordMovement(
                $material->product,
                $warehouse,
                $material->planned_quantity,
                MovementType::ProductionConsumption,
                (float) $material->product->average_cost,
                $order->order_number,
                "Consumo en OP {$order->order_number}",
                auth()->id(),
                $order
            );

            $material->update([
                'consumed_quantity' => $material->planned_quantity,
                'unit_cost' => $material->product->average_cost,
            ]);
        }

        $actualMaterial = $order->materials->sum(fn($m) => $m->planned_quantity * $m->unit_cost);
        $order->update(['actual_material_cost' => $actualMaterial]);
    }

    private function onFinish(ProductionOrder $order): void
    {
        if ($order->produced_quantity <= 0) {
            throw new \DomainException('Debe registrar al menos una unidad producida antes de finalizar la OP.');
        }

        $order->update(['finished_at' => now()]);
    }

    private function onCancel(ProductionOrder $order): void
    {
        // Si ya estaba en proceso, devolver materiales al inventario
        if ($order->status === ProductionOrderStatus::InProgress) {
            $warehouse = Warehouse::findOrFail($order->warehouse_id);
            foreach ($order->materials as $material) {
                if ($material->consumed_quantity > 0) {
                    $this->stockService->recordMovement(
                        $material->product,
                        $warehouse,
                        $material->consumed_quantity,
                        MovementType::PositiveAdjustment,
                        (float) $material->unit_cost,
                        $order->order_number,
                        "Devolución por cancelación de OP {$order->order_number}",
                        auth()->id()
                    );
                }
            }
        }
    }

    private function createMaterials(ProductionOrder $order, Recipe $recipe): void
    {
        $recipe->load('ingredients.product');
        $factor = $order->planned_quantity / $recipe->yield_quantity;

        foreach ($recipe->ingredients as $ingredient) {
            $order->materials()->create([
                'product_id' => $ingredient->product_id,
                'planned_quantity' => round($ingredient->net_quantity_calculated * $factor, 4),
                'consumed_quantity' => 0,
                'unit' => $ingredient->unit,
                'unit_cost' => $ingredient->product->average_cost,
                'is_reserved' => false,
            ]);
        }
    }

    private function calculateEstimatedCosts(ProductionOrder $order, Recipe $recipe): void
    {
        $factor = $order->planned_quantity / max(1, $recipe->yield_quantity);
        $materialCost = $recipe->material_cost * $factor;
        $laborCost = $recipe->labor_cost * $factor;
        $overheadCost = $recipe->overhead_cost * $factor;
        $totalCost = $materialCost + $laborCost + $overheadCost;

        $order->update([
            'estimated_material_cost' => round($materialCost, 4),
            'estimated_labor_cost' => round($laborCost, 4),
            'estimated_overhead_cost' => round($overheadCost, 4),
            'estimated_total_cost' => round($totalCost, 4),
        ]);
    }

    private function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $seq = DB::selectOne("SELECT nextval('production_order_seq') AS seq")->seq;

        return sprintf('OP-%s-%05d', $year, $seq);
    }
}
