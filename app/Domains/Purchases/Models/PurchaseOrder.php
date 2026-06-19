<?php

namespace App\Domains\Purchases\Models;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Users\Models\User;
use App\Support\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseOrder extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number', 'status', 'supplier_id', 'warehouse_id', 'order_date',
        'expected_date', 'received_date', 'subtotal', 'tax_rate', 'tax_amount',
        'discount_amount', 'shipping_cost', 'total', 'currency', 'exchange_rate',
        'payment_terms', 'delivery_terms', 'reference', 'created_by', 'approved_by',
        'approved_at', 'notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'order_date' => 'date',
            'expected_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'shipping_cost' => 'decimal:4',
            'total' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'approved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total'])
            ->logOnlyDirty();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getReceivedPercentAttribute(): float
    {
        $totalOrdered = $this->items->sum('quantity');
        $totalReceived = $this->items->sum('received_quantity');

        if ($totalOrdered <= 0) {
            return 0;
        }

        return round(($totalReceived / $totalOrdered) * 100, 1);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $tax = $subtotal * ($this->tax_rate / 100);
        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax + $this->shipping_cost - $this->discount_amount,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::Draft->value,
            PurchaseOrderStatus::Sent->value,
            PurchaseOrderStatus::PartiallyReceived->value,
        ]);
    }
}
