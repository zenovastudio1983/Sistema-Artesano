<?php

namespace App\Domains\Sales\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'description', 'quantity', 'unit',
        'unit_price', 'unit_cost', 'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:4',
            'subtotal' => 'decimal:4',
            'cost_total' => 'decimal:4',
            'margin' => 'decimal:4',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->subtotal <= 0) {
            return 0;
        }

        return round(($this->margin / $this->subtotal) * 100, 2);
    }
}
