<?php

namespace App\Domains\Production\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderMaterial extends Model
{
    protected $fillable = [
        'production_order_id', 'product_id', 'planned_quantity', 'consumed_quantity',
        'unit', 'unit_cost', 'is_reserved',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'decimal:4',
            'consumed_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'is_reserved' => 'boolean',
        ];
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->planned_quantity - $this->consumed_quantity);
    }
}
