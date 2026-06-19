<?php

namespace App\Domains\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'business_name', 'trade_name', 'tax_id', 'tax_type', 'email', 'phone',
        'mobile', 'website', 'address', 'city', 'country', 'contact_name',
        'contact_email', 'contact_phone', 'payment_days', 'currency', 'credit_limit',
        'current_balance', 'rating', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?? $this->business_name;
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
