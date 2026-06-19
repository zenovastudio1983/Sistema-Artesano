<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_id', 'warehouse_id', 'quantity', 'reserved_quantity', 'average_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reserved_quantity' => 'decimal:4',
            'available_quantity' => 'decimal:4',
            'average_cost' => 'decimal:4',
            'total_value' => 'decimal:4',
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

    public function isLow(): bool
    {
        return $this->quantity <= $this->product->stock_minimum
            && $this->product->stock_minimum > 0;
    }

    public function isCritical(): bool
    {
        return $this->quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) return 'out_of_stock';
        if ($this->quantity <= $this->product->stock_minimum) return 'critical';
        if ($this->quantity <= $this->product->stock_minimum * 1.5) return 'low';
        return 'ok';
    }
}
