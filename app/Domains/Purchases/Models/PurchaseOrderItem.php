<?php

namespace App\Domains\Purchases\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'description', 'quantity',
        'received_quantity', 'unit', 'unit_price', 'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:4',
            'subtotal' => 'decimal:4',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }
}
