<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\Models\Inventory;
use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Products\Models\Product;
use App\Support\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    public function recordMovement(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        MovementType $type,
        float $unitCost = 0,
        ?string $referenceNumber = null,
        ?string $notes = null,
        ?int $createdBy = null,
        mixed $moveable = null,
        ?Warehouse $destinationWarehouse = null
    ): StockMovement {
        return DB::transaction(function () use (
            $product, $warehouse, $quantity, $type, $unitCost,
            $referenceNumber, $notes, $createdBy, $moveable, $destinationWarehouse
        ) {
            $inventory = $this->getOrCreateInventory($product, $warehouse);

            $balanceBefore = (float) $inventory->quantity;
            $newQuantity = $type->isEntry()
                ? $balanceBefore + $quantity
                : $balanceBefore - $quantity;

            if ($newQuantity < 0 && !in_array($type, [MovementType::NegativeAdjustment])) {
                throw new \DomainException(
                    "Stock insuficiente para {$product->name}. Disponible: {$balanceBefore}, Requerido: {$quantity}"
                );
            }

            $newAverageCost = $this->calculateNewAverageCost(
                $inventory, $quantity, $unitCost, $type
            );

            $inventory->update([
                'quantity' => max(0, $newQuantity),
                'average_cost' => $newAverageCost,
                'updated_at' => now(),
            ]);

            $movement = StockMovement::create([
                'reference_number' => $referenceNumber,
                'movement_type' => $type,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'destination_warehouse_id' => $destinationWarehouse?->id,
                'quantity' => abs($quantity),
                'unit_cost' => $unitCost > 0 ? $unitCost : $inventory->average_cost,
                'balance_quantity' => max(0, $newQuantity),
                'balance_average_cost' => $newAverageCost,
                'balance_total_value' => max(0, $newQuantity) * $newAverageCost,
                'moveable_type' => $moveable ? get_class($moveable) : null,
                'moveable_id' => $moveable?->id,
                'notes' => $notes,
                'created_by' => $createdBy ?? auth()->id(),
                'moved_at' => now(),
            ]);

            $product->update([
                'average_cost' => $newAverageCost,
                'cost' => $newAverageCost,
                'updated_at' => now(),
            ]);

            return $movement;
        });
    }

    public function transfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float $quantity,
        ?string $notes = null,
        ?int $createdBy = null
    ): array {
        return DB::transaction(function () use ($product, $fromWarehouse, $toWarehouse, $quantity, $notes, $createdBy) {
            $reference = 'TRF-' . now()->format('YmdHis');
            $inventoryFrom = $this->getOrCreateInventory($product, $fromWarehouse);
            $avgCost = (float) $inventoryFrom->average_cost;

            $exit = $this->recordMovement(
                $product, $fromWarehouse, $quantity,
                MovementType::TransferOut, $avgCost,
                $reference, $notes, $createdBy,
                null, $toWarehouse
            );

            $entry = $this->recordMovement(
                $product, $toWarehouse, $quantity,
                MovementType::TransferIn, $avgCost,
                $reference, $notes, $createdBy,
                null, $fromWarehouse
            );

            return ['exit' => $exit, 'entry' => $entry];
        });
    }

    public function adjust(
        Product $product,
        Warehouse $warehouse,
        float $newQuantity,
        ?string $notes = null,
        ?int $createdBy = null
    ): StockMovement {
        $inventory = $this->getOrCreateInventory($product, $warehouse);
        $currentQty = (float) $inventory->quantity;
        $difference = $newQuantity - $currentQty;

        if ($difference == 0) {
            throw new \DomainException('La cantidad ajustada es igual al stock actual.');
        }

        $type = $difference > 0
            ? MovementType::PositiveAdjustment
            : MovementType::NegativeAdjustment;

        return $this->recordMovement(
            $product, $warehouse, abs($difference), $type,
            (float) $inventory->average_cost,
            'ADJ-' . now()->format('YmdHis'),
            $notes, $createdBy
        );
    }

    public function reserveStock(Product $product, Warehouse $warehouse, float $quantity): void
    {
        DB::transaction(function () use ($product, $warehouse, $quantity) {
            $inventory = $this->getOrCreateInventory($product, $warehouse);

            if ((float) $inventory->available_quantity < $quantity) {
                throw new \DomainException(
                    "Stock disponible insuficiente para reservar. Disponible: {$inventory->available_quantity}"
                );
            }

            $inventory->increment('reserved_quantity', $quantity);
        });
    }

    public function releaseReservation(Product $product, Warehouse $warehouse, float $quantity): void
    {
        $inventory = $this->getOrCreateInventory($product, $warehouse);
        $inventory->decrement('reserved_quantity', min($quantity, $inventory->reserved_quantity));
    }

    public function getKardex(Product $product, Warehouse $warehouse, ?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        $query = StockMovement::with(['product', 'warehouse', 'createdBy'])
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('moved_at')
            ->orderBy('id');

        if ($from) {
            $query->where('moved_at', '>=', $from);
        }

        if ($to) {
            $query->where('moved_at', '<=', $to . ' 23:59:59');
        }

        return $query->get();
    }

    public function getLowStockProducts(): \Illuminate\Support\Collection
    {
        return Product::with(['inventory.warehouse'])
            ->active()
            ->whereRaw(
                'EXISTS (
                    SELECT 1 FROM inventory i
                    JOIN warehouses w ON w.id = i.warehouse_id
                    WHERE i.product_id = products.id
                    AND w.is_active = TRUE
                    AND products.stock_minimum > 0
                    GROUP BY i.product_id
                    HAVING SUM(i.quantity) <= products.stock_minimum
                )'
            )
            ->get();
    }

    private function getOrCreateInventory(Product $product, Warehouse $warehouse): Inventory
    {
        return Inventory::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
            ['quantity' => 0, 'reserved_quantity' => 0, 'average_cost' => $product->cost]
        );
    }

    private function calculateNewAverageCost(
        Inventory $inventory,
        float $quantity,
        float $unitCost,
        MovementType $type
    ): float {
        if (!$type->isEntry() || $unitCost <= 0) {
            return (float) $inventory->average_cost;
        }

        $currentQty = (float) $inventory->quantity;
        $currentCost = (float) $inventory->average_cost;
        $currentValue = $currentQty * $currentCost;
        $newValue = $quantity * $unitCost;
        $totalQty = $currentQty + $quantity;

        if ($totalQty <= 0) {
            return $unitCost;
        }

        return round(($currentValue + $newValue) / $totalQty, 4);
    }
}
