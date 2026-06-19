<?php

namespace App\Domains\Recipes\Models;

use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'name', 'description', 'version', 'is_active', 'is_default',
        'yield_quantity', 'yield_unit', 'material_cost', 'labor_cost', 'overhead_cost',
        'total_cost', 'unit_cost', 'production_time_minutes', 'instructions', 'notes', 'costed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'yield_quantity' => 'decimal:4',
            'material_cost' => 'decimal:4',
            'labor_cost' => 'decimal:4',
            'overhead_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'costed_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function additionalCosts()
    {
        return $this->hasMany(RecipeCost::class);
    }

    public function calculateMaterialCost(): float
    {
        return $this->ingredients->sum('total_cost');
    }

    public function calculateTotalCost(): float
    {
        return $this->material_cost + $this->labor_cost + $this->overhead_cost;
    }

    public function calculateUnitCost(): float
    {
        if ($this->yield_quantity <= 0) {
            return 0;
        }

        return round($this->calculateTotalCost() / $this->yield_quantity, 4);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
