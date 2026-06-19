<?php

namespace App\Domains\Recipes\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeCost extends Model
{
    protected $fillable = [
        'recipe_id', 'cost_type', 'description', 'amount', 'is_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'is_per_unit' => 'boolean',
        ];
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
