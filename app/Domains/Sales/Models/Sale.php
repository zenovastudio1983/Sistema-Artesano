<?php

namespace App\Domains\Sales\Models;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Users\Models\User;
use App\Support\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sale extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number', 'status', 'type', 'customer_id', 'warehouse_id', 'sale_date',
        'due_date', 'delivery_date', 'confirmed_at', 'subtotal', 'discount_percent',
        'discount_amount', 'tax_rate', 'tax_amount', 'total', 'cost_of_goods',
        'currency', 'exchange_rate', 'invoice_number', 'invoice_series', 'invoice_date',
        'payment_method', 'payment_terms', 'shipping_address', 'reference',
        'seller_id', 'created_by', 'notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => SaleStatus::class,
            'sale_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'invoice_date' => 'date',
            'confirmed_at' => 'datetime',
            'subtotal' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:4',
            'total' => 'decimal:4',
            'cost_of_goods' => 'decimal:4',
            'gross_profit' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'meta' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total'])
            ->logOnlyDirty();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total - $this->total_paid);
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->total <= 0) {
            return 0;
        }

        return round(($this->gross_profit / $this->total) * 100, 2);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $discountAmount = $subtotal * ($this->discount_percent / 100);
        $netSubtotal = $subtotal - $discountAmount;
        $tax = $netSubtotal * ($this->tax_rate / 100);
        $costOfGoods = $this->items->sum('cost_total');

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $tax,
            'total' => $netSubtotal + $tax,
            'cost_of_goods' => $costOfGoods,
        ]);
    }

    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', [
            SaleStatus::Confirmed->value,
            SaleStatus::Invoiced->value,
            SaleStatus::Paid->value,
        ]);
    }

    public function scopeInMonth($query, int $year, int $month)
    {
        return $query->whereYear('sale_date', $year)->whereMonth('sale_date', $month);
    }
}
