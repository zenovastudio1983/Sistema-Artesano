<?php

namespace App\Domains\Recipes\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id', 'product_id', 'quantity', 'unit', 'scrap_percentage',
        'unit_cost', 'is_optional', 'notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'scrap_percentage' => 'decimal:2',
            'net_quantity' => 'decimal:6',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'is_optional' => 'boolean',
        ];
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getNetQuantityCalculatedAttribute(): float
    {
        return round($this->quantity * (1 + $this->scrap_percentage / 100), 6);
    }

    public function getTotalCostCalculatedAttribute(): float
    {
        return round($this->net_quantity_calculated * $this->unit_cost, 4);
    }
}
