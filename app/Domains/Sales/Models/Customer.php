<?php

namespace App\Domains\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'business_name', 'trade_name', 'tax_id', 'tax_type', 'customer_type',
        'email', 'phone', 'mobile', 'address', 'city', 'country', 'contact_name',
        'payment_days', 'credit_limit', 'current_balance', 'discount_percent',
        'price_list', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?? $this->business_name;
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->sales()
            ->whereIn('status', ['confirmed', 'invoiced', 'paid'])
            ->sum('total');
    }

    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('business_name', 'ilike', "%{$term}%")
                ->orWhere('trade_name', 'ilike', "%{$term}%")
                ->orWhere('tax_id', 'ilike', "%{$term}%")
                ->orWhere('code', 'ilike', "%{$term}%");
        });
    }
}
