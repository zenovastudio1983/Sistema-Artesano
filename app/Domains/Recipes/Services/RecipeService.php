<?php

namespace App\Domains\Recipes\Services;

use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Models\Recipe;
use App\Domains\Recipes\Models\RecipeIngredient;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function create(Product $product, array $data, array $ingredients, array $additionalCosts = []): Recipe
    {
        return DB::transaction(function () use ($product, $data, $ingredients, $additionalCosts) {
            $version = Recipe::where('product_id', $product->id)->max('version') + 1;

            if ($data['is_default'] ?? false) {
                Recipe::where('product_id', $product->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $recipe = Recipe::create([
                ...$data,
                'product_id' => $product->id,
                'version' => $version,
            ]);

            foreach ($ingredients as $index => $ingredient) {
                $ingredientProduct = Product::findOrFail($ingredient['product_id']);
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $ingredientProduct->id,
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit'],
                    'scrap_percentage' => $ingredient['scrap_percentage'] ?? 0,
                    'unit_cost' => (float) $ingredientProduct->average_cost,
                    'sort_order' => $index,
                    'is_optional' => $ingredient['is_optional'] ?? false,
                    'notes' => $ingredient['notes'] ?? null,
                ]);
            }

            foreach ($additionalCosts as $cost) {
                $recipe->additionalCosts()->create($cost);
            }

            $this->recalculateCosts($recipe);

            return $recipe->fresh(['ingredients', 'additionalCosts']);
        });
    }

    public function recalculateCosts(Recipe $recipe): Recipe
    {
        $recipe->load(['ingredients.product', 'additionalCosts']);

        $materialCost = 0;
        foreach ($recipe->ingredients as $ingredient) {
            $currentCost = (float) $ingredient->product->average_cost;
            $ingredient->update(['unit_cost' => $currentCost]);
            $materialCost += $ingredient->net_quantity_calculated * $currentCost;
        }

        $laborCost = 0;
        $overheadCost = 0;
        foreach ($recipe->additionalCosts as $cost) {
            if ($cost->cost_type === 'labor') {
                $laborCost += $cost->amount;
            } else {
                $overheadCost += $cost->amount;
            }
        }

        $laborCost += (float) $recipe->labor_cost;
        $overheadCost += (float) $recipe->overhead_cost;
        $totalCost = $materialCost + $laborCost + $overheadCost;
        $unitCost = $recipe->yield_quantity > 0
            ? round($totalCost / $recipe->yield_quantity, 4)
            : 0;

        $recipe->update([
            'material_cost' => round($materialCost, 4),
            'total_cost' => round($totalCost, 4),
            'unit_cost' => $unitCost,
            'costed_at' => now(),
        ]);

        return $recipe->fresh();
    }

    public function recalculateAllCostsForProduct(Product $product): void
    {
        $product->recipes()->active()->each(fn($recipe) => $this->recalculateCosts($recipe));

        $defaultRecipe = $product->defaultRecipe;
        if ($defaultRecipe) {
            $product->update([
                'standard_cost' => $defaultRecipe->unit_cost,
                'cost' => $defaultRecipe->unit_cost,
            ]);
        }
    }

    public function duplicate(Recipe $recipe, bool $setAsDefault = false): Recipe
    {
        return DB::transaction(function () use ($recipe, $setAsDefault) {
            $newRecipe = $recipe->replicate();
            $newRecipe->version = Recipe::where('product_id', $recipe->product_id)->max('version') + 1;
            $newRecipe->name = $recipe->name . ' (copia)';
            $newRecipe->is_default = $setAsDefault;
            $newRecipe->costed_at = null;
            $newRecipe->save();

            foreach ($recipe->ingredients as $ingredient) {
                $newIngredient = $ingredient->replicate();
                $newIngredient->recipe_id = $newRecipe->id;
                $newIngredient->save();
            }

            foreach ($recipe->additionalCosts as $cost) {
                $newCost = $cost->replicate();
                $newCost->recipe_id = $newRecipe->id;
                $newCost->save();
            }

            if ($setAsDefault) {
                Recipe::where('product_id', $recipe->product_id)
                    ->where('id', '!=', $newRecipe->id)
                    ->update(['is_default' => false]);
            }

            return $newRecipe->fresh(['ingredients', 'additionalCosts']);
        });
    }

    public function checkMaterialAvailability(Recipe $recipe, float $quantity, int $warehouseId): array
    {
        $availability = [];

        foreach ($recipe->ingredients as $ingredient) {
            $needed = $ingredient->net_quantity_calculated * $quantity;
            $inventory = \App\Domains\Inventory\Models\Inventory::where('product_id', $ingredient->product_id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $available = $inventory ? (float) $inventory->available_quantity : 0;

            $availability[] = [
                'product' => $ingredient->product,
                'needed' => $needed,
                'available' => $available,
                'sufficient' => $available >= $needed,
                'shortage' => max(0, $needed - $available),
            ];
        }

        return $availability;
    }
}
