<?php

namespace App\Domains\Purchases\Models;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceipt extends Model
{
    protected $fillable = [
        'receipt_number', 'purchase_order_id', 'warehouse_id', 'receipt_date',
        'supplier_invoice', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
