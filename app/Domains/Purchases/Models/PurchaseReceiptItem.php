<?php

namespace App\Domains\Purchases\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_receipt_id', 'purchase_order_item_id', 'product_id',
        'quantity', 'unit_price', 'batch_number', 'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'expiry_date' => 'date',
        ];
    }

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
