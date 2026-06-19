<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Products\Models\Product;
use App\Domains\Users\Models\User;
use App\Support\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'reference_number', 'movement_type', 'product_id', 'warehouse_id',
        'destination_warehouse_id', 'quantity', 'unit_cost',
        'balance_quantity', 'balance_average_cost', 'balance_total_value',
        'moveable_type', 'moveable_id', 'notes', 'created_by', 'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'balance_quantity' => 'decimal:4',
            'balance_average_cost' => 'decimal:4',
            'balance_total_value' => 'decimal:4',
            'moved_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function moveable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEntry(): bool
    {
        return $this->movement_type->isEntry();
    }

    public function isExit(): bool
    {
        return $this->movement_type->isExit();
    }

    public function getSignedQuantityAttribute(): float
    {
        return $this->isEntry() ? $this->quantity : -$this->quantity;
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeInDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('moved_at', [$from, $to]);
    }

    public function scopeEntries($query)
    {
        return $query->whereIn('movement_type', [
            MovementType::PurchaseEntry->value,
            MovementType::ProductionEntry->value,
            MovementType::PositiveAdjustment->value,
            MovementType::TransferIn->value,
            MovementType::CustomerReturn->value,
            MovementType::InitialStock->value,
        ]);
    }

    public function scopeExits($query)
    {
        return $query->whereIn('movement_type', [
            MovementType::SaleExit->value,
            MovementType::ProductionConsumption->value,
            MovementType::NegativeAdjustment->value,
            MovementType::TransferOut->value,
            MovementType::ReturnToSupplier->value,
        ]);
    }
}
